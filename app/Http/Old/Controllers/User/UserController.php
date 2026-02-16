<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\User\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class UserController extends BaseController
{
    /**
     * Cadastra um novo usuário
     * Sistema SIMPLIFICADO - apenas o essencial para funcionar
     */
    public function store(): RedirectResponse
    {
        // Recebe dados
        $name = trim($this->request->getPost('name') ?? '');
        $email = strtolower(trim($this->request->getPost('email') ?? ''));
        $password = $this->request->getPost('password') ?? '';
        $passwordConfirm = $this->request->getPost('password_confirm') ?? '';
        $typeUser = $this->request->getPost('type_user') ?? '';

        // Validação SIMPLES
        if (empty($name) || strlen($name) < 3) {
            return redirect()->to('/auth/login')
                ->with('error', 'Nome deve ter no mínimo 3 caracteres.');
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->to('/auth/login')
                ->with('error', 'E-mail inválido.');
        }

        if (empty($password) || strlen($password) < 8) {
            return redirect()->to('/auth/login')
                ->with('error', 'Senha deve ter no mínimo 8 caracteres.');
        }

        if ($password !== $passwordConfirm) {
            return redirect()->to('/auth/login')
                ->with('error', 'As senhas não conferem.');
        }

        if (empty($typeUser) || !in_array($typeUser, ['1', '2'])) {
            return redirect()->to('/auth/login')
                ->with('error', 'Selecione um tipo de usuário.');
        }

        // Instancia model
        $userModel = new UserModel();
        $userModel->skipValidation(true); // Desabilita validação do model

        // Verifica email duplicado
        if ($userModel->where('email', $email)->first()) {
            return redirect()->to('/auth/login')
                ->with('error', 'E-mail já cadastrado. Faça login.');
        }

        // Prepara dados
        $data = [
            'name'      => $name,
            'email'     => $email,
            'password'  => password_hash($password, PASSWORD_DEFAULT),
            'role'      => 'client',
            'type_user' => (int) $typeUser,
            'is_active' => 1
        ];

        // Insere no banco
        try {
            $inserted = $userModel->insert($data);
            
            if (!$inserted) {
                return redirect()->to('/auth/login')
                    ->with('error', 'Erro ao cadastrar. Tente novamente.');
            }

            // SUCESSO
            return redirect()->to('/auth/login')
                ->with('success', 'Usuário cadastrado com sucesso! Faça login.');

        } catch (\Exception $e) {
            log_message('error', 'Erro ao cadastrar: ' . $e->getMessage());
            return redirect()->to('/auth/login')
                ->with('error', 'Erro ao cadastrar. Tente novamente.');
        }
    }
}
