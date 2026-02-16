<?php

namespace App\Controllers\DashAdmin;

use App\Controllers\BaseController;
use App\Models\User\UserModel;
use App\Models\User\PerfilUsuarioModel;
use App\Models\Perfil\PerfilBarClientModel;
use App\Models\User\UserPostModel;
use App\Models\Eventos\EvtEventoModel;

class EstabelecimentoController extends BaseController
{
    public function index()
    {
        $session = session();
        if (!$session->get('logged') || (int)($session->get('type_user') ?? 0) !== 3) {
            return redirect()->to('/dashboard')->with('error', 'Acesso restrito.');
        }

        $userModel = new UserModel();
        // Buscar usuários do tipo 1 (Estabelecimento/Barista)
        // Filtra por deleted_at null para pegar apenas ativos, mas podemos querer ver todos
        $users = $userModel->where('type_user', 1)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return view('dash_admin/estabelecimentos/index', ['users' => $users]);
    }

    public function create()
    {
        $session = session();
        if (!$session->get('logged') || (int)($session->get('type_user') ?? 0) !== 3) {
            return redirect()->to('/dashboard')->with('error', 'Acesso restrito.');
        }

        return view('dash_admin/estabelecimentos/create');
    }

    public function store()
    {
        $session = session();
        if (!$session->get('logged') || (int)($session->get('type_user') ?? 0) !== 3) {
            return redirect()->to('/dashboard')->with('error', 'Acesso restrito.');
        }

        $email = trim($this->request->getPost('email') ?? '');
        $password = $this->request->getPost('password');
        $name = trim($this->request->getPost('name') ?? '');
        $barName = trim($this->request->getPost('bar_name') ?? '');

        if (!$email || !$password || !$name || !$barName) {
            return redirect()->back()->withInput()->with('error', 'Todos os campos são obrigatórios.');
        }

        $userModel = new UserModel();
        if ($userModel->where('email', $email)->first()) {
            return redirect()->back()->withInput()->with('error', 'E-mail já cadastrado.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // 1. Criar Usuário
            $userData = [
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'type_user' => 1, // Barista/Estabelecimento
                'is_active' => 1,
                'role' => 'client',
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $userId = $userModel->insert($userData);
            
            if (!$userId) {
                throw new \Exception('Falha ao criar usuário.');
            }

            // 2. Criar Perfil de Usuário
            $perfilUsuarioModel = new PerfilUsuarioModel();
            $perfilData = [
                'user_id' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $perfilUsuarioModel->insert($perfilData);

            // 3. Criar Perfil do Estabelecimento (Bar)
            $barModel = new PerfilBarClientModel();
            $barData = [
                'user_id' => $userId,
                'nome' => $barName,
                'endereco' => 'Endereço não informado', // Valor padrão inicial
                'status' => 'ativo',
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $barModel->insert($barData);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Erro na transação do banco de dados.');
            }

            return redirect()->to('/dashboard/admin/estabelecimentos')->with('success', 'Estabelecimento criado com sucesso!');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Erro ao criar: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $session = session();
        if (!$session->get('logged') || (int)($session->get('type_user') ?? 0) !== 3) {
            return redirect()->to('/dashboard')->with('error', 'Acesso restrito.');
        }

        $userModel = new UserModel();
        $user = $userModel->find($id);

        if (!$user) {
            return redirect()->to('/dashboard/admin/estabelecimentos')->with('error', 'Usuário não encontrado.');
        }

        // Dados adicionais para o Hub
        $barModel = new PerfilBarClientModel();
        $bar = $barModel->where('user_id', $id)->first();

        $postModel = new UserPostModel();
        $postsCount = $postModel->where('user_id', $id)->countAllResults();

        $eventoModel = new EvtEventoModel();
        $eventosCount = 0;
        if ($bar && isset($bar['bares_id'])) {
            $eventosCount = $eventoModel->where('bares_id', $bar['bares_id'])->countAllResults();
        }

        return view('dash_admin/estabelecimentos/show', [
            'user' => $user,
            'bar' => $bar,
            'stats' => [
                'posts' => $postsCount,
                'eventos' => $eventosCount
            ]
        ]);
    }

    public function loginAs($userId)
    {
        $session = session();
        // Check Admin
        if (!$session->get('logged') || (int)($session->get('type_user') ?? 0) !== 3) {
            return redirect()->to('/auth/login');
        }

        $userModel = new UserModel();
        $user = $userModel->find($userId);
        
        if (!$user) {
            return redirect()->back()->with('error', 'Usuário não encontrado');
        }

        // Salvar sessão de admin para retorno
        $adminId = $session->get('user_id');
        $adminName = $session->get('name');

        // Destruir sessão atual (parcialmente ou regenerar)
        // Melhor apenas sobrescrever as chaves de usuário
        
        $session->set('original_admin_id', $adminId);
        $session->set('original_admin_name', $adminName);
        
        // Set User Session
        $sessionData = [
            'user_id'   => $user['id'],
            'name'      => $user['name'],
            'email'     => $user['email'],
            'role'      => $user['role'],
            'type_user' => (int) $user['type_user'],
            'logged'    => true,
            'login_time' => time(),
        ];

        $session->set($sessionData);
        
        // Pega o destino se houver
        $redirect = $this->request->getGet('redirect') ?? '/dashboard';
        
        return redirect()->to($redirect)->with('success', 'Acessando como ' . $user['name']);
    }
}
