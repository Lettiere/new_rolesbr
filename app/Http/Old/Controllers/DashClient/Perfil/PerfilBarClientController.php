<?php

namespace App\Controllers\DashClient\Perfil;

use App\Controllers\BaseController;
use App\Models\Perfil\PerfilBarClientModel;
use App\Models\Perfil\FotoBarModel;
use App\Models\Eventos\EvtEventoModel;
use App\Models\Eventos\EvtIngressoVendidoModel;
use CodeIgniter\Database\BaseBuilder;

class PerfilBarClientController extends BaseController
{
    public function index()
    {
        return view('dash_client/perfil/bar/index');
    }

    public function delete($id)
    {
        $session = session();
        $userId = $session->get('user_id');
        $model = new PerfilBarClientModel();

        // Verifica permissão e existência
        $bar = $model->where('user_id', $userId)->find($id);
        if (!$bar) {
            return redirect()->back()->with('error', 'Permissão negada ou bar não encontrado.');
        }

        // Soft Delete (assumindo que useSoftDeletes está true no Model, se não estiver, será hard delete)
        if ($model->delete($id)) {
            return redirect()->to('/dashboard/perfil')->with('success', 'Estabelecimento removido com sucesso.');
        }

        return redirect()->back()->with('error', 'Erro ao remover estabelecimento.');
    }

    public function deleteFoto($idFoto)
    {
        $session = session();
        $userId = $session->get('user_id');
        
        $fotoModel = new FotoBarModel();
        $barModel = new PerfilBarClientModel();

        // Buscar foto
        $foto = $fotoModel->find($idFoto);
        if (!$foto) {
            return redirect()->back()->with('error', 'Foto não encontrada.');
        }

        // Verificar se o bar da foto pertence ao usuário
        $bar = $barModel->where('user_id', $userId)->find($foto['bares_id']);
        if (!$bar) {
            return redirect()->back()->with('error', 'Sem permissão para excluir esta foto.');
        }

        // Remover arquivo físico
        $filePath = ROOTPATH . 'public/' . $foto['url'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Remover do banco
        $fotoModel->delete($idFoto);

        return redirect()->back()->with('success', 'Foto removida com sucesso.');
    }

    public function view($id)
    {
        $model = new PerfilBarClientModel();
        $fotoModel = new FotoBarModel();
        $eventoModel = new EvtEventoModel();
        $ingressoModel = new EvtIngressoVendidoModel();

        $bar = $model->find($id);
        if (!$bar) {
            return redirect()->to('/dashboard/perfil/bar')->with('error', 'Bar não encontrado.');
        }

        $fotos = $fotoModel->where('bares_id', $id)->findAll();

        // Carregar eventos do bar e montar resumo (Total, M, H, LGBT)
        $eventos = $eventoModel->where('bares_id', $id)->orderBy('data_inicio', 'DESC')->findAll(12);
        $eventosResumo = [];
        foreach ($eventos as $evt) {
            $eventoId = (int) $evt['evento_id'];
            $total = $ingressoModel->where('evento_id', $eventoId)->countAllResults();
            // Contagem por gênero via join
            $builder = $ingressoModel->builder();
            $builder->select('COALESCE(base_genero_usuario.nome, "Outro") as genero_nome, COUNT(*) as qtd')
                    ->join('perfil_usuarios_tb', 'perfil_usuarios_tb.user_id = evt_ingressos_vendidos_tb.user_id', 'left')
                    ->join('base_genero_usuario', 'base_genero_usuario.genero_id = perfil_usuarios_tb.genero_id', 'left')
                    ->where('evt_ingressos_vendidos_tb.evento_id', $eventoId)
                    ->groupBy('genero_nome');
            $rows = $builder->get()->getResultArray();
            $m = 0; $h = 0; $lgbt = 0;
            foreach ($rows as $r) {
                $gn = $r['genero_nome'] ?? 'Outro';
                $q = (int) ($r['qtd'] ?? 0);
                if (stripos($gn, 'Femin') !== false) $m += $q; // Mulheres
                elseif (stripos($gn, 'Masc') !== false) $h += $q; // Homens
                else $lgbt += $q; // Outros/LGBT
            }
            $eventosResumo[] = [
                'evento_id' => $eventoId,
                'nome'      => $evt['nome'],
                'data'      => $evt['data_inicio'],
                'total'     => $total,
                'm'         => $m,
                'h'         => $h,
                'lgbt'      => $lgbt,
            ];
        }

        return view('dash_client/perfil/ver_bar', [
            'bar' => $bar,
            'fotos' => $fotos,
            'eventos_resumo' => $eventosResumo,
            'isFollowing' => $isFollowing,
            'followersCount' => $followersCount,
            'currentUserId' => $currentUserId
        ]);
    }

    public function edit($id)
    {
        $session = session();
        $userId = $session->get('user_id');

        $model = new PerfilBarClientModel();
        $fotoModel = new FotoBarModel();

        // Buscar bar e verificar se pertence ao usuário
        $bar = $model->where('user_id', $userId)->find($id);

        if (!$bar) {
            return redirect()->to('/dashboard/perfil')->with('error', 'Estabelecimento não encontrado ou você não tem permissão para editá-lo.');
        }

        // Buscar fotos
        $fotos = $fotoModel->where('bares_id', $id)->findAll();

        return view('dash_client/perfil/editar_bar', [
            'bar' => $bar,
            'fotos' => $fotos
        ]);
    }

    public function update($id)
    {
        $session = session();
        $userId = $session->get('user_id');
        
        $model = new PerfilBarClientModel();
        $fotoModel = new FotoBarModel();

        // Verificar permissão
        $bar = $model->where('user_id', $userId)->find($id);
        if (!$bar) {
            return redirect()->back()->with('error', 'Sem permissão para editar.');
        }

        $post = $this->request->getPost();

        // Processar Benefícios (JSON) - Merge com existentes ou substituir? Vamos substituir com o que vier do form
        // Se o form envia JSON vazio, significa que o usuário removeu tudo ou não mexeu.
        // O JS deve garantir que envie o estado atual.
        $beneficios = [
            'comodidades'    => json_decode($post['comodidades_json'] ?? '[]', true),
            'entretenimento' => json_decode($post['entretenimento_json'] ?? '[]', true),
            'ofertas'        => json_decode($post['ofertas_json'] ?? '[]', true),
            'servicos'       => json_decode($post['servicos_json'] ?? '[]', true),
        ];

        $data = [
            'nome'           => trim($post['nome'] ?? ''),
            'telefone'       => trim($post['telefone'] ?? ''),
            'endereco'       => trim($post['endereco'] ?? ''),
            'estado_id'      => !empty($post['estado_id']) ? (int) $post['estado_id'] : null,
            'cidade_id'      => !empty($post['cidade_id']) ? (int) $post['cidade_id'] : null,
            'bairro_id'      => !empty($post['bairro_id']) ? (int) $post['bairro_id'] : null,
            'bairro_nome'    => isset($post['bairro_nome']) ? trim($post['bairro_nome']) : null,
            'povoado_id'     => !empty($post['povoado_id']) ? (int) $post['povoado_id'] : null,
            'prefixo_rua_id' => !empty($post['prefixo_rua_id']) ? (int) $post['prefixo_rua_id'] : null,
            'rua_id'         => !empty($post['rua_id']) ? (int) $post['rua_id'] : null,
            'latitude'       => !empty($post['latitude']) ? (float) $post['latitude'] : null,
            'longitude'      => !empty($post['longitude']) ? (float) $post['longitude'] : null,
            'capacidade'     => !empty($post['capacidade']) ? (int) $post['capacidade'] : null,
            'horario_inicio' => trim($post['horario_inicio'] ?? ''),
            'horario_final'  => trim($post['horario_final'] ?? ''),
            'tipo_bar'       => !empty($post['tipo_bar']) ? (int) $post['tipo_bar'] : null,
            'descricao'      => trim($post['descricao'] ?? ''),
            'beneficios'     => json_encode($beneficios),
            // Status pode ser atualizado se tiver campo, senão mantém
        ];

        // Upload Nova Imagem de Perfil (se enviada)
        $imgPerfil = $this->request->getFile('imagem');
        $perfilAtualizado = false;
        if ($imgPerfil && $imgPerfil->isValid() && !$imgPerfil->hasMoved()) {
            $allowedExts = ['jpg','jpeg','png','gif','webp'];
            $allowedMimes = ['image/jpeg','image/jpg','image/pjpeg','image/png','image/gif','image/webp'];
            $ext = strtolower($imgPerfil->getClientExtension() ?: $imgPerfil->getExtension());
            $mime = strtolower($imgPerfil->getMimeType() ?: '');
            if (!in_array($ext, $allowedExts) || !in_array($mime, $allowedMimes)) {
                log_message('error', 'Formato de imagem de perfil não permitido: ' . $ext . ' ' . $mime);
            } else {
                $uploadsRoot = ROOTPATH . 'public/uploads';
                if (!is_dir($uploadsRoot)) mkdir($uploadsRoot, 0777, true);
                $baresRoot = $uploadsRoot . '/bares';
                if (!is_dir($baresRoot)) mkdir($baresRoot, 0777, true);
                $basePath = $baresRoot . '/' . $id . '/perfil';
                if (!is_dir($baresRoot . '/' . $id)) mkdir($baresRoot . '/' . $id, 0777, true);
                if (!is_dir($basePath)) mkdir($basePath, 0777, true);

                $newName = $imgPerfil->getRandomName();
                if ($imgPerfil->move($basePath, $newName)) {
                    $perfilAtualizado = true;
                } else {
                    log_message('error', 'Falha ao mover imagem de perfil');
                }
                
                // Remover imagem antiga se necessário (opcional)
                
                $data['imagem'] = 'uploads/bares/' . $id . '/perfil/' . $newName;
            }
        }

        if (!$model->update($id, $data)) {
            return redirect()->back()->withInput()->with('error', 'Erro ao atualizar.');
        }

        $files = $this->request->getFileMultiple('galeria_fotos');
        if (empty($files)) {
            $allFiles = $this->request->getFiles();
            if (isset($allFiles['galeria_fotos'])) {
                $files = $allFiles['galeria_fotos'];
            }
        }
        if ($files instanceof \CodeIgniter\HTTP\Files\UploadedFile) {
            $files = [$files];
        }
        $uploadedCount = 0;
        if (is_array($files) && !empty($files)) {
            $uploadsRoot = ROOTPATH . 'public/uploads';
            if (!is_dir($uploadsRoot)) mkdir($uploadsRoot, 0777, true);
            $baresRoot = $uploadsRoot . '/bares';
            if (!is_dir($baresRoot)) mkdir($baresRoot, 0777, true);
            $barRoot = $baresRoot . '/' . $id;
            if (!is_dir($barRoot)) mkdir($barRoot, 0777, true);
            $galeriaPath = $barRoot . '/galeria';
            if (!is_dir($galeriaPath)) mkdir($galeriaPath, 0777, true);
            foreach ($files as $file) {
                if ($file && $file->isValid() && !$file->hasMoved()) {
                    $allowedExts = ['jpg','jpeg','png','gif','webp'];
                    $allowedMimes = ['image/jpeg','image/jpg','image/pjpeg','image/png','image/gif','image/webp'];
                    $ext = strtolower($file->getClientExtension() ?: $file->getExtension());
                    $mime = strtolower($file->getMimeType() ?: '');
                    if (!in_array($ext, $allowedExts) || !in_array($mime, $allowedMimes)) {
                        log_message('error', 'Formato de imagem de galeria não permitido: ' . $ext . ' ' . $mime);
                        continue;
                    }
                    $newName = $file->getRandomName();
                    if ($file->move($galeriaPath, $newName)) {
                        $ok = $fotoModel->insert([
                            'bares_id' => $id,
                            'url'      => 'uploads/bares/' . $id . '/galeria/' . $newName,
                            'descricao'=> 'Foto da galeria'
                        ]);
                        $uploadedCount++;
                        if (!$ok) {
                            log_message('error', 'Falha ao inserir foto da galeria no banco (update)');
                        }
                    } else {
                        log_message('error', 'Falha ao mover arquivo de galeria');
                    }
                } else {
                    if (method_exists($file, 'getErrorString')) {
                        log_message('error', 'Erro no upload de galeria: ' . $file->getErrorString());
                    }
                }
            }
        }

        $msg = 'Atualizado com sucesso!';
        if ($perfilAtualizado) {
            $msg .= ' Imagem de perfil atualizada.';
        }
        if ($uploadedCount > 0) {
            $msg .= ' ' . $uploadedCount . ' foto(s) adicionada(s) à galeria.';
        }
        return redirect()->to('/dashboard/perfil/bar/view/' . $id)->with('success', $msg);
    }

    public function store()
    {
        $session = session();
        $model   = new PerfilBarClientModel();
        $fotoModel = new FotoBarModel();

        // Usuário autenticado
        $userId = $session->get('user_id');
        if (!$userId) {
             return redirect()->back()->with('error', 'Usuário não autenticado.');
        }

        // POST
        $post = $this->request->getPost();

        // Processar Benefícios (JSON unificado)
        $beneficios = [
            'comodidades'    => json_decode($post['comodidades_json'] ?? '[]', true),
            'entretenimento' => json_decode($post['entretenimento_json'] ?? '[]', true),
            'ofertas'        => json_decode($post['ofertas_json'] ?? '[]', true),
            'servicos'       => json_decode($post['servicos_json'] ?? '[]', true),
        ];

        // 1. DADOS INICIAIS DO BAR (sem imagem ainda)
        $data = [
            'user_id'        => (int) $userId,
            'nome'           => trim($post['nome'] ?? ''),
            'telefone'       => trim($post['telefone'] ?? ''),
            'endereco'       => trim($post['endereco'] ?? ''),
            'estado_id'      => !empty($post['estado_id']) ? (int) $post['estado_id'] : null,
            'cidade_id'      => !empty($post['cidade_id']) ? (int) $post['cidade_id'] : null,
            'bairro_id'      => !empty($post['bairro_id']) ? (int) $post['bairro_id'] : null,
            'bairro_nome'    => isset($post['bairro_nome']) ? trim($post['bairro_nome']) : null,
            'povoado_id'     => !empty($post['povoado_id']) ? (int) $post['povoado_id'] : null,
            'prefixo_rua_id' => !empty($post['prefixo_rua_id']) ? (int) $post['prefixo_rua_id'] : null,
            'rua_id'         => !empty($post['rua_id']) ? (int) $post['rua_id'] : null,
            'latitude'       => !empty($post['latitude']) ? (float) $post['latitude'] : null,
            'longitude'      => !empty($post['longitude']) ? (float) $post['longitude'] : null,
            'capacidade'     => !empty($post['capacidade']) ? (int) $post['capacidade'] : null,
            'horario_inicio' => trim($post['horario_inicio'] ?? ''),
            'horario_final'  => trim($post['horario_final'] ?? ''),
            'tipo_bar'       => !empty($post['tipo_bar']) ? (int) $post['tipo_bar'] : null,
            'descricao'      => trim($post['descricao'] ?? ''),
            'beneficios'     => json_encode($beneficios),
            'imagem'         => '', // Placeholder, será atualizado após insert
            'status'         => 'ativo'
        ];

        if (empty($data['latitude']) || empty($data['longitude'])) {
            $data['latitude'] = null;
            $data['longitude'] = null;
        }

        // INSERT para gerar o ID
        try {
            if (!$model->insert($data)) {
                $errors = $model->errors();
                return redirect()->back()->withInput()->with('error', 'Erro ao salvar o bar: ' . implode(', ', $errors));
            }
            
            $barId = $model->getInsertID();

            // Caminhos das pastas baseados no ID
            $uploadsRoot = ROOTPATH . 'public/uploads';
            if (!is_dir($uploadsRoot)) mkdir($uploadsRoot, 0777, true);
            $baresRoot = $uploadsRoot . '/bares';
            if (!is_dir($baresRoot)) mkdir($baresRoot, 0777, true);
            $basePath = $baresRoot . '/' . $barId;
            if (!is_dir($basePath)) mkdir($basePath, 0777, true);
            $pathPerfil = $basePath . '/perfil';
            $pathGaleria = $basePath . '/galeria';

            // Criar pastas se não existirem
            if (!is_dir($pathPerfil)) {
                mkdir($pathPerfil, 0777, true);
            }
            if (!is_dir($pathGaleria)) {
                mkdir($pathGaleria, 0777, true);
            }

            // 2. Upload Imagem Principal (Agora salvando na pasta do ID)
            $imgPerfil = $this->request->getFile('imagem');
            $perfilAtualizado = false;
            if ($imgPerfil && $imgPerfil->isValid() && !$imgPerfil->hasMoved()) {
                $allowedExts = ['jpg','jpeg','png','gif','webp'];
                $allowedMimes = ['image/jpeg','image/jpg','image/pjpeg','image/png','image/gif','image/webp'];
                $ext = strtolower($imgPerfil->getClientExtension() ?: $imgPerfil->getExtension());
                $mime = strtolower($imgPerfil->getMimeType() ?: '');
                if (!in_array($ext, $allowedExts) || !in_array($mime, $allowedMimes)) {
                    log_message('error', 'Formato de imagem de perfil não permitido (store): ' . $ext . ' ' . $mime);
                } else {
                    $newName = $imgPerfil->getRandomName();
                    if ($imgPerfil->move($pathPerfil, $newName)) {
                        $perfilAtualizado = true;
                    } else {
                        log_message('error', 'Falha ao mover imagem de perfil (store)');
                    }
                    
                    // Atualizar registro com o caminho da imagem
                    $caminhoRelativo = 'uploads/bares/' . $barId . '/perfil/' . $newName;
                    $model->update($barId, ['imagem' => $caminhoRelativo]);
                }
            }

            $files = $this->request->getFileMultiple('galeria_fotos');
            if (empty($files)) {
                $allFiles = $this->request->getFiles();
                if (isset($allFiles['galeria_fotos'])) {
                    $files = $allFiles['galeria_fotos'];
                }
            }
            if ($files instanceof \CodeIgniter\HTTP\Files\UploadedFile) {
                $files = [$files];
            }
            $uploadedCount = 0;
            if (is_array($files) && !empty($files)) {
                foreach ($files as $file) {
                    if ($file && $file->isValid() && !$file->hasMoved()) {
                        $allowedExts = ['jpg','jpeg','png','gif','webp'];
                        $allowedMimes = ['image/jpeg','image/png','image/gif','image/webp'];
                        $ext = strtolower($file->getClientExtension() ?: $file->getExtension());
                        $mime = strtolower($file->getMimeType() ?: '');
                        if (!in_array($ext, $allowedExts) || !in_array($mime, $allowedMimes)) {
                            log_message('error', 'Formato de imagem de galeria não permitido (store): ' . $ext . ' ' . $mime);
                            continue;
                        }
                        $newName = $file->getRandomName();
                        if ($file->move($pathGaleria, $newName)) {
                            $ok = $fotoModel->insert([
                                'bares_id' => $barId,
                                'url'      => 'uploads/bares/' . $barId . '/galeria/' . $newName,
                                'descricao'=> 'Foto da galeria'
                            ]);
                            $uploadedCount++;
                            if (!$ok) {
                                log_message('error', 'Falha ao inserir foto da galeria no banco (store)');
                            }
                        } else {
                            log_message('error', 'Falha ao mover arquivo de galeria (store)');
                        }
                    } else {
                        if (method_exists($file, 'getErrorString')) {
                            log_message('error', 'Erro no upload de galeria (store): ' . $file->getErrorString());
                        }
                    }
                }
            }

            $msg = 'Bar cadastrado com sucesso!';
            if ($perfilAtualizado) {
                $msg .= ' Imagem de perfil enviada.';
            }
            if ($uploadedCount > 0) {
                $msg .= ' ' . $uploadedCount . ' foto(s) adicionada(s) à galeria.';
            }
            return redirect()->to('/dashboard/perfil/bar/view/' . $barId)->with('success', $msg);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao inserir bar: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Erro ao salvar o bar. Tente novamente.');
        }
    }

    public function getCidades($estadoId)
    {
        $model = new \App\Models\Localizacao\CidadeModel();
        $rows = $model->where('estado_id', (int) $estadoId)->orderBy('nome', 'ASC')->findAll();
        return $this->response->setJSON($rows);
    }

    public function getEstados()
    {
        $model = new \App\Models\Localizacao\EstadoModel();
        $rows = $model->where('status', 1)->orderBy('nome', 'ASC')->findAll();
        return $this->response->setJSON($rows);
    }

    public function getBairros($cidadeId)
    {
        $model = new \App\Models\Localizacao\BairroModel();
        $q = trim($this->request->getGet('q') ?? '');
        $builder = $model->where('cidade_id', (int) $cidadeId)->orderBy('nome', 'ASC');
        if ($q !== '') {
            $builder = $builder->like('nome', $q);
        }
        $rows = $builder->findAll();
        return $this->response->setJSON($rows);
    }

    public function getPovoados($cidadeId)
    {
        $model = new \App\Models\Localizacao\PovoadoModel();
        $rows = $model->where('cidade_id', (int) $cidadeId)->orderBy('nome', 'ASC')->findAll();
        return $this->response->setJSON($rows);
    }

    public function getPrefixos()
    {
        try {
            // Se existir o model, usa; senão retorna lista vazia
            if (class_exists('\\App\\Models\\Localizacao\\RuaPrefixoModel')) {
                $model = new \App\Models\Localizacao\RuaPrefixoModel();
                $rows = $model->orderBy('nome', 'ASC')->findAll();
                return $this->response->setJSON($rows);
            }
        } catch (\Throwable $e) {}
        return $this->response->setJSON([]);
    }

    public function storeBairro()
    {
        $cidadeId = (int) ($this->request->getPost('cidade_id') ?? 0);
        $nome = trim($this->request->getPost('nome') ?? '');
        if (!$cidadeId || $nome === '') {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'Cidade e nome são obrigatórios']);
        }
        $model = new \App\Models\Localizacao\BairroModel();
        $data = ['cidade_id' => $cidadeId, 'nome' => $nome, 'status' => 1];
        try {
            $id = $model->insert($data);
            return $this->response->setJSON(['id' => $id, 'nome' => $nome]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Falha ao cadastrar bairro']);
        }
    }
}
