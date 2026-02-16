<?php

namespace App\Controllers\DashClient\Perfil;

use App\Controllers\BaseController;
use App\Models\Perfil;
use App\Models\User\GeneroUsuarioModel;
use App\Models\Localizacao\CidadeModel;
use App\Models\Localizacao\EstadoModel;

class UserProfileController extends BaseController
{
    protected $perfilModel;
    protected $generoModel;
    protected $cidadeModel;
    protected $estadoModel;

    public function __construct()
    {
        $this->perfilModel = new Perfil();
        $this->generoModel = new GeneroUsuarioModel();
        $this->cidadeModel = new CidadeModel();
        $this->estadoModel = new EstadoModel();
    }

    // Exibir Perfil
    public function index()
    {
        $userId = session()->get('user_id');
        $perfilBasico = $this->perfilModel->where('user_id', $userId)->first();
        if (!$perfilBasico) {
            return redirect()->to('/dashboard/perfil/create');
        }

        $perfilCompleto = $this->perfilModel->getFullProfile($userId) ?? $perfilBasico;
        $user = (new \App\Models\User\UserModel())->find($userId) ?? [];
        $perfilCompleto['display_name'] = $user['name'] ?? 'Usuário';
        $perfilCompleto['display_email'] = $user['email'] ?? '';
        return view('dash_client/perfil_usuario/show', ['perfil' => $perfilCompleto]);
    }

    // Formulário de Criação/Edição
    public function create()
    {
        $userId = session()->get('user_id');
        $perfil = $this->perfilModel->where('user_id', $userId)->first();

        // Se já existe, redireciona para edição
        if ($perfil) {
            return redirect()->to('/dashboard/perfil/edit');
        }

        $generos = $this->generoModel->where('ativo', 1)->findAll();
        $estados = $this->estadoModel->where('status', 1)->orderBy('nome', 'ASC')->findAll();
        $cidades = [];

        return view('dash_client/perfil_usuario/form', [
            'perfil' => null,
            'generos' => $generos,
            'estados' => $estados,
            'cidades' => $cidades
        ]);
    }

    public function edit()
    {
        $userId = session()->get('user_id');
        $perfil = $this->perfilModel->where('user_id', $userId)->first();

        if (!$perfil) {
            return redirect()->to('/dashboard/perfil/create');
        }

        $generos = $this->generoModel->where('ativo', 1)->findAll();
        $estados = $this->estadoModel->where('status', 1)->orderBy('nome', 'ASC')->findAll();
        
        $cidades = [];
        $estado_selecionado = null;

        if ($perfil['cidade_id']) {
            $cidade = $this->cidadeModel->find($perfil['cidade_id']);
            if ($cidade) {
                $estado_selecionado = $cidade['estado_id'];
                $cidades = $this->cidadeModel->where('estado_id', $estado_selecionado)->orderBy('nome', 'ASC')->findAll();
            }
        }

        return view('dash_client/perfil_usuario/form', [
            'perfil' => $perfil,
            'generos' => $generos,
            'estados' => $estados,
            'cidades' => $cidades,
            'estado_selecionado' => $estado_selecionado
        ]);
    }

    public function show($userId)
    {
        $perfilBasico = $this->perfilModel->where('user_id', (int)$userId)->first();
        if (!$perfilBasico) {
            return redirect()->to('/dashboard/perfil'); // fallback
        }
        $perfilCompleto = $this->perfilModel->getFullProfile((int)$userId) ?? $perfilBasico;
        $user = (new \App\Models\User\UserModel())->find((int)$userId) ?? [];
        $perfilCompleto['display_name'] = $user['name'] ?? 'Usuário';
        $perfilCompleto['display_email'] = $user['email'] ?? '';
        return view('dash_client/perfil_usuario/show', ['perfil' => $perfilCompleto]);
    }

    public function store()
    {
        $userId = session()->get('user_id');
        $post = $this->request->getPost();
        
        // Validação básica (CPF removido da obrigatoriedade conforme solicitado)
        if (empty($post['genero_id'])) {
            return redirect()->back()->withInput()->with('error', 'Preencha o Gênero.');
        }

        $cpfRaw = isset($post['cpf']) ? preg_replace('/[^0-9]/', '', $post['cpf']) : '';
        
        $data = [
            'user_id' => (int) $userId,
            'cpf' => $cpfRaw ?: null, // Salva null se vazio
            'rg' => $post['rg'] ?? null,
            'telefone' => $post['telefone'] ?? null,
            'data_nascimento' => $post['data_nascimento'] ?? null,
            'genero_id' => !empty($post['genero_id']) ? (int) $post['genero_id'] : null,
            'estado_id' => !empty($post['estado_id']) ? (int) $post['estado_id'] : null,
            'cidade_id' => !empty($post['cidade_id']) ? (int) $post['cidade_id'] : null,
            'bairro_id' => !empty($post['bairro_id']) ? (int) $post['bairro_id'] : null,
            'bairro_nome' => isset($post['bairro_nome']) ? trim($post['bairro_nome']) : null,
            'rua_id' => !empty($post['rua_id']) ? (int) $post['rua_id'] : null,
            'bio' => $post['bio'] ?? null
        ];

        // Upload de Foto
        $img = $this->request->getFile('foto_perfil');
        if ($img && $img->isValid() && !$img->hasMoved()) {
            $newName = $img->getRandomName();
            $path = 'uploads/perfis/' . $userId . '/perfil';
            
            if (!is_dir(ROOTPATH . 'public/' . $path)) {
                mkdir(ROOTPATH . 'public/' . $path, 0777, true);
            }
            
            $img->move(ROOTPATH . 'public/' . $path, $newName);
            $data['foto_perfil'] = $path . '/' . $newName;
            
            // Registrar mídia independente
            $mediaModel = new \App\Models\User\UserMediaModel();
            $mediaModel->insert([
                'user_id' => (int) $userId,
                'tipo'    => 'perfil_foto',
                'url'     => $data['foto_perfil'],
            ]);
        }

        // Verifica se é update ou insert
        $existing = $this->perfilModel->where('user_id', $userId)->first();
        
        if ($existing) {
            // Se tiver foto antiga e subiu nova, deletar a antiga
            if (isset($data['foto_perfil']) && $existing['foto_perfil'] && file_exists(ROOTPATH . 'public/' . $existing['foto_perfil'])) {
                unlink(ROOTPATH . 'public/' . $existing['foto_perfil']);
            }
            
            $this->perfilModel->update($existing['perfil_id'], $data);
            $msg = 'Perfil atualizado com sucesso.';
        } else {
            $this->perfilModel->insert($data);
            $msg = 'Perfil criado com sucesso.';
        }

        return redirect()->to('/dashboard/perfil')->with('success', $msg);
    }

    // AJAX para carregar cidades por estado
    public function getCidades($estadoId)
    {
        $cidades = $this->cidadeModel->where('estado_id', $estadoId)->orderBy('nome', 'ASC')->findAll();
        return $this->response->setJSON($cidades);
    }
}
