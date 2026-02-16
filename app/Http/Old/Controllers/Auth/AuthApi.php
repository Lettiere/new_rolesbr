<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\User\UserModel;
use App\Models\User\PerfilUsuarioModel;

class AuthApi extends BaseController
{
    private function validateCPF(string $cpf): bool
    {
        $cpf = preg_replace('/\D+/', '', $cpf);
        if (strlen($cpf) !== 11) return false;
        if (preg_match('/^(\d)\1{10}$/', $cpf)) return false;
        $sum = 0;
        for ($i = 0, $w = 10; $i < 9; $i++, $w--) { $sum += intval($cpf[$i]) * $w; }
        $d1 = 11 - ($sum % 11); if ($d1 > 9) { $d1 = 0; }
        if ($d1 !== intval($cpf[9])) return false;
        $sum = 0;
        for ($i = 0, $w = 11; $i < 10; $i++, $w--) { $sum += intval($cpf[$i]) * $w; }
        $d2 = 11 - ($sum % 11); if ($d2 > 9) { $d2 = 0; }
        return $d2 === intval($cpf[10]);
    }

    public function login()
    {
        $email = strtolower(trim((string) ($this->request->getPost('email') ?? '')));
        $password = (string) ($this->request->getPost('password') ?? '');
        if ($email === '' || $password === '') {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'error' => 'Dados inválidos']);
        }
        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();
        if (!$user || !password_verify($password, $user['password'] ?? '')) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'error' => 'Credenciais inválidas']);
        }
        if (!($user['is_active'] ?? 1)) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'error' => 'Usuário inativo']);
        }
        session()->set([
            'logged' => true,
            'user_id' => (int) ($user['id'] ?? 0),
            'name' => $user['name'] ?? '',
            'email' => $user['email'] ?? '',
            'role' => $user['role'] ?? 'client',
            'type_user' => (int) ($user['type_user'] ?? 2),
        ]);
        return $this->response->setJSON([
            'success' => true,
            'user' => [
                'id' => (int) ($user['id'] ?? 0),
                'name' => $user['name'] ?? '',
                'email' => $user['email'] ?? '',
                'role' => $user['role'] ?? 'client',
                'type_user' => (int) ($user['type_user'] ?? 2),
            ]
        ]);
    }

    public function register()
    {
        $name = trim((string) ($this->request->getPost('name') ?? ''));
        $email = strtolower(trim((string) ($this->request->getPost('email') ?? '')));
        $password = (string) ($this->request->getPost('password') ?? '');
        $passwordConfirm = (string) ($this->request->getPost('password_confirm') ?? '');
        $cpf = preg_replace('/\D+/', '', (string) ($this->request->getPost('cpf') ?? ''));
        $telefone = trim((string) ($this->request->getPost('telefone') ?? ''));
        if ($name === '' || strlen($name) < 3) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'error' => 'Nome inválido']);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'error' => 'E-mail inválido']);
        }
        if ($password === '' || $password !== $passwordConfirm) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'error' => 'Senha inválida']);
        }
        if (!$this->validateCPF($cpf)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'error' => 'CPF inválido']);
        }
        $userModel = new UserModel();
        if ($userModel->where('email', $email)->first()) {
            return $this->response->setStatusCode(409)->setJSON(['success' => false, 'error' => 'E-mail já cadastrado']);
        }
        $perfilModel = new PerfilUsuarioModel();
        if ($perfilModel->where('cpf', $cpf)->first()) {
            return $this->response->setStatusCode(409)->setJSON(['success' => false, 'error' => 'CPF já cadastrado']);
        }
        $dataUser = [
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'client',
            'type_user' => 2,
            'is_active' => 1
        ];
        try {
            $userId = (int) $userModel->insert($dataUser);
            if ($userId <= 0) {
                return $this->response->setStatusCode(500)->setJSON(['success' => false, 'error' => 'Erro ao cadastrar usuário']);
            }
            $perfilModel->insert([
                'user_id' => $userId,
                'cpf' => $cpf,
                'telefone' => $telefone
            ]);
            return $this->response->setJSON([
                'success' => true,
                'user' => [
                    'id' => $userId,
                    'name' => $name,
                    'email' => $email
                ]
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'error' => 'Erro no cadastro']);
        }
    }
}

