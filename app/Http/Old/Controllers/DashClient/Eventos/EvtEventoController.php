<?php

namespace App\Controllers\DashClient\Eventos;

use App\Controllers\BaseController;
use App\Models\Eventos\EvtEventoModel;
use App\Models\Eventos\EvtTipoEventoModel;
use App\Models\Eventos\EvtIngressoVendidoModel;
use App\Models\Perfil\PerfilBarClientModel;
use App\Models\User\PerfilUsuarioModel;

class EvtEventoController extends BaseController
{
    protected $eventoModel;
    protected $tipoEventoModel;
    protected $ingressoVendidoModel;
    protected $perfilUsuarioModel;
    protected $barModel;

    public function __construct()
    {
        $this->eventoModel = new EvtEventoModel();
        $this->tipoEventoModel = new EvtTipoEventoModel();
        $this->ingressoVendidoModel = new EvtIngressoVendidoModel();
        $this->perfilUsuarioModel = new PerfilUsuarioModel();
        $this->barModel = new PerfilBarClientModel();
    }

    private function checkPermission($barId)
    {
        $userId = session()->get('user_id');
        $bar = $this->barModel->where('user_id', $userId)->find($barId);
        return $bar;
    }

    public function index($barId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $eventos = $this->eventoModel->where('bares_id', $barId)->orderBy('data_inicio', 'DESC')->findAll();
        
        // Carregar nome do tipo de evento
        foreach ($eventos as &$evt) {
            $tipo = $this->tipoEventoModel->find($evt['tipo_evento_id']);
            $evt['tipo_nome'] = $tipo ? $tipo['nome'] : 'Não definido';
        }

        return view('dash_client/eventos/eventos/index', [
            'bar_id' => $barId,
            'eventos' => $eventos
        ]);
    }

    public function create($barId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $tipos = $this->tipoEventoModel->where('ativo', 1)->findAll();

        return view('dash_client/eventos/eventos/cadastro', [
            'bar_id' => $barId,
            'tipos' => $tipos
        ]);
    }

    public function store($barId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $post = $this->request->getPost();

        // Gerar slug básico
        $slug = url_title($post['nome'], '-', true);

        $data = [
            'bares_id' => $barId,
            'tipo_evento_id' => $post['tipo_evento_id'],
            'nome' => $post['nome'],
            'slug' => $slug,
            'descricao' => $post['descricao'],
            'data_inicio' => $post['data_inicio'],
            'data_fim' => $post['data_fim'],
            'hora_abertura_portas' => $post['hora_abertura_portas'] ?? null,
            'lotacao_maxima' => $post['lotacao_maxima'] ?? null,
            'idade_minima' => $post['idade_minima'] ?? 18,
            'status' => $post['status'],
            'visibilidade' => $post['visibilidade'],
            'video_youtube_url' => $post['video_youtube_url'] ?? null
        ];

        // Upload de imagem
        $img = $this->request->getFile('imagem_capa');
        if ($img && $img->isValid() && !$img->hasMoved()) {
            $newName = $img->getRandomName();
            $path = 'uploads/eventos/' . $barId; // Organizar por bar
            
            if (!is_dir(ROOTPATH . 'public/' . $path)) {
                mkdir(ROOTPATH . 'public/' . $path, 0777, true);
            }
            
            $img->move(ROOTPATH . 'public/' . $path, $newName);
            $data['imagem_capa'] = $path . '/' . $newName;
        }

        if ($this->eventoModel->insert($data)) {
            return redirect()->to("/dashboard/perfil/bar/{$barId}/eventos")->with('success', 'Evento criado com sucesso.');
        }

        return redirect()->back()->withInput()->with('error', 'Erro ao criar evento.');
    }

    public function edit($barId, $eventoId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $evento = $this->eventoModel->find($eventoId);
        if (!$evento || $evento['bares_id'] != $barId) {
            return redirect()->back()->with('error', 'Evento não encontrado.');
        }

        $tipos = $this->tipoEventoModel->where('ativo', 1)->findAll();

        return view('dash_client/eventos/eventos/edit', [
            'bar_id' => $barId,
            'evento' => $evento,
            'tipos' => $tipos
        ]);
    }

    public function update($barId, $eventoId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $evento = $this->eventoModel->find($eventoId);
        if (!$evento || $evento['bares_id'] != $barId) {
            return redirect()->back()->with('error', 'Evento não encontrado.');
        }

        $post = $this->request->getPost();
        
        $data = [
            'tipo_evento_id' => $post['tipo_evento_id'],
            'nome' => $post['nome'],
            'descricao' => $post['descricao'],
            'data_inicio' => $post['data_inicio'],
            'data_fim' => $post['data_fim'],
            'hora_abertura_portas' => $post['hora_abertura_portas'] ?? null,
            'lotacao_maxima' => $post['lotacao_maxima'] ?? null,
            'idade_minima' => $post['idade_minima'] ?? 18,
            'status' => $post['status'],
            'visibilidade' => $post['visibilidade'],
            'video_youtube_url' => $post['video_youtube_url'] ?? null
        ];

        // Upload de imagem
        $img = $this->request->getFile('imagem_capa');
        if ($img && $img->isValid() && !$img->hasMoved()) {
            $newName = $img->getRandomName();
            $path = 'uploads/eventos/' . $barId;
            
            if (!is_dir(ROOTPATH . 'public/' . $path)) {
                mkdir(ROOTPATH . 'public/' . $path, 0777, true);
            }
            
            $img->move(ROOTPATH . 'public/' . $path, $newName);
            
            // Remover imagem antiga se existir
            if ($evento['imagem_capa'] && file_exists(ROOTPATH . 'public/' . $evento['imagem_capa'])) {
                unlink(ROOTPATH . 'public/' . $evento['imagem_capa']);
            }
            
            $data['imagem_capa'] = $path . '/' . $newName;
        }

        if ($this->eventoModel->update($eventoId, $data)) {
            return redirect()->to("/dashboard/perfil/bar/{$barId}/eventos")->with('success', 'Evento atualizado com sucesso.');
        }

        return redirect()->back()->withInput()->with('error', 'Erro ao atualizar evento.');
    }

    public function show($barId, $eventoId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $evento = $this->eventoModel->find($eventoId);
        if (!$evento || $evento['bares_id'] != $barId) {
            return redirect()->back()->with('error', 'Evento não encontrado.');
        }
        
        $tipo = $this->tipoEventoModel->find($evento['tipo_evento_id']);
        $evento['tipo_nome'] = $tipo ? $tipo['nome'] : 'Não definido';

        // Insights de Ingressos
        $totalVendidos = $this->ingressoVendidoModel->where('evento_id', $eventoId)->countAllResults();
        
        // Insights por Gênero
        $generos = [
            'Masculino' => 0,
            'Feminino' => 0,
            'Outro' => 0,
            'Nao Informado' => 0
        ];
        
        $vendas = $this->ingressoVendidoModel->where('evento_id', $eventoId)->findAll();
        foreach ($vendas as $venda) {
            if ($venda['user_id']) {
                $perfil = $this->perfilUsuarioModel->where('user_id', $venda['user_id'])->first();
                if ($perfil) {
                    $gen = $perfil['genero'] ?? 'Nao Informado';
                    if (isset($generos[$gen])) {
                        $generos[$gen]++;
                    } else {
                        $generos['Nao Informado']++;
                    }
                } else {
                    $generos['Nao Informado']++;
                }
            } else {
                $generos['Nao Informado']++;
            }
        }

        return view('dash_client/eventos/eventos/show', [
            'bar_id' => $barId,
            'evento' => $evento,
            'insights' => [
                'total_vendidos' => $totalVendidos,
                'generos' => $generos
            ]
        ]);
    }
    
    // API para buscar compradores via AJAX
    public function getCompradores($barId, $eventoId)
    {
        if (!$this->checkPermission($barId)) {
            return $this->response->setJSON(['error' => 'Permissão negada'])->setStatusCode(403);
        }

        $limit = $this->request->getGet('limit') ?? 10;
        $offset = $this->request->getGet('offset') ?? 0;
        $search = $this->request->getGet('search');

        $builder = $this->ingressoVendidoModel->builder();
        $builder->select('evt_ingressos_vendidos_tb.*, evt_lotes_ingressos_tb.nome as nome_lote, perfil_usuarios_tb.foto_perfil, perfil_usuarios_tb.cpf, base_genero_usuario.nome as genero, users.name as user_name');
        $builder->join('evt_lotes_ingressos_tb', 'evt_lotes_ingressos_tb.lote_id = evt_ingressos_vendidos_tb.lote_id');
        $builder->join('perfil_usuarios_tb', 'perfil_usuarios_tb.user_id = evt_ingressos_vendidos_tb.user_id', 'left');
        $builder->join('base_genero_usuario', 'base_genero_usuario.genero_id = perfil_usuarios_tb.genero_id', 'left');
        $builder->join('users', 'users.id = evt_ingressos_vendidos_tb.user_id', 'left');
        $builder->where('evt_ingressos_vendidos_tb.evento_id', $eventoId);

        if ($search) {
            $builder->groupStart();
            $builder->like('evt_ingressos_vendidos_tb.nome_comprador', $search);
            $builder->orLike('evt_ingressos_vendidos_tb.email_comprador', $search);
            $builder->orLike('evt_ingressos_vendidos_tb.codigo_unico', $search);
            $builder->orLike('users.name', $search);
            $cpfDigits = preg_replace('/\D+/', '', $search);
            if (!empty($cpfDigits)) {
                $builder->orLike('perfil_usuarios_tb.cpf', $cpfDigits);
            }
            $builder->groupEnd();
        }

        $total = $builder->countAllResults(false);
        $builder->orderBy('evt_ingressos_vendidos_tb.data_compra', 'DESC');
        $builder->limit($limit, $offset);
        
        $compradores = $builder->get()->getResultArray();

        return $this->response->setJSON([
            'data' => $compradores,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }
    
    public function delete($barId, $eventoId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }
        
        $evento = $this->eventoModel->find($eventoId);
        if (!$evento || $evento['bares_id'] != $barId) {
            return redirect()->back()->with('error', 'Evento não encontrado.');
        }
        
        // Remover imagem se existir
        if ($evento['imagem_capa'] && file_exists(ROOTPATH . 'public/' . $evento['imagem_capa'])) {
            unlink(ROOTPATH . 'public/' . $evento['imagem_capa']);
        }
        
        $this->eventoModel->delete($eventoId);
        
        return redirect()->to("/dashboard/perfil/bar/{$barId}/eventos")->with('success', 'Evento removido.');
    }
}
