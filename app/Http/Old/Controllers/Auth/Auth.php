<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\User\UserModel;
use CodeIgniter\HTTP\RedirectResponse;
use Config\Services;

class Auth extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Exibe a tela de login
     */
    public function login()
    {
        // Se já estiver logado, redireciona
        if (session()->get('logged')) {
            $typeUser = (int) session()->get('type_user');
            if ($typeUser === 3) {
                return redirect()->to(site_http('dashboard/admin'));
            }
            if ($typeUser === 2) {
                return redirect()->to(site_http('dashboard/user'));
            }
            return redirect()->to(site_http('dashboard'));
        }

        return view('login/auth');
    }

    /**
     * Processa o login - SIMPLIFICADO
     */
    public function attemptLogin(): RedirectResponse
    {
        $email = trim($this->request->getPost('email') ?? '');
        $password = $this->request->getPost('password') ?? '';

        // Validação básica
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'E-mail inválido.');
        }

        if (empty($password)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Senha obrigatória.');
        }

        // Busca usuário
        $user = $this->userModel
            ->withDeleted()
            ->where('email', strtolower($email))
            ->first();
        if (!$user) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'E-mail ou senha incorretos.');
        }
        if (!empty($user['deleted_at']) && (int)($user['is_active'] ?? 0) === 1) {
            try {
                $this->userModel->update((int)$user['id'], ['deleted_at' => null]);
                $user['deleted_at'] = null;
            } catch (\Throwable $e) {}
        }
        if ((int)($user['is_active'] ?? 0) !== 1) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Conta inativa. Entre em contato com o suporte.');
        }

        if (!$user) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'E-mail ou senha incorretos.');
        }

        $passOk = false;
        if (is_string($user['password'])) {
            if (password_verify($password, $user['password'])) {
                $passOk = true;
                if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                    try {
                        $this->userModel->update((int)$user['id'], ['password' => password_hash($password, PASSWORD_DEFAULT)]);
                        $user['password'] = password_hash($password, PASSWORD_DEFAULT);
                    } catch (\Throwable $e) {}
                }
            } else {
                $legacyMd5 = (bool) preg_match('/^[a-f0-9]{32}$/i', $user['password']);
                if ($legacyMd5 && md5($password) === strtolower($user['password'])) {
                    $passOk = true;
                    try {
                        $this->userModel->update((int)$user['id'], ['password' => password_hash($password, PASSWORD_DEFAULT)]);
                        $user['password'] = password_hash($password, PASSWORD_DEFAULT);
                    } catch (\Throwable $e) {}
                } elseif ($password === $user['password']) {
                    $passOk = true;
                    try {
                        $this->userModel->update((int)$user['id'], ['password' => password_hash($password, PASSWORD_DEFAULT)]);
                        $user['password'] = password_hash($password, PASSWORD_DEFAULT);
                    } catch (\Throwable $e) {}
                }
            }
        }
        if (!$passOk) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'E-mail ou senha incorretos.');
        }

        // LOGIN OK - Cria sessão
        $sessionData = [
            'user_id'   => $user['id'],
            'name'      => $user['name'],
            'email'     => $user['email'],
            'role'      => $user['role'],
            'type_user' => (int) $user['type_user'],
            'logged'    => true,
            'login_time' => time(),
        ];

        session()->set($sessionData);
        session()->regenerate(true);

        // REDIRECIONAMENTO BASEADO EM type_user
        $typeUser = (int) $user['type_user'];
        
        if ($typeUser === 1) {
            // Barista -> Dashboard Cliente
            return redirect()->to(site_http('dashboard'))
                ->with('success', 'Login realizado com sucesso!');
        } elseif ($typeUser === 2) {
            // Usuário comum -> Dashboard User
            return redirect()->to(site_http('dashboard/user'))
                ->with('success', 'Login realizado com sucesso!');
        } elseif ($typeUser === 3) {
            return redirect()->to(site_http('dashboard/admin'))
                ->with('success', 'Login realizado com sucesso!');
        }

        // Fallback
        return redirect()->to(site_http('dashboard'))
            ->with('success', 'Login realizado com sucesso!');
    }

    /**
     * Logout
     */
    public function logout(): RedirectResponse
    {
        session()->destroy();
        return redirect()->to(site_http('auth/login'))
            ->with('success', 'Logout realizado com sucesso!');
    }

    /**
     * DEV ONLY: reset de senha por e-mail
     */
    public function devReset(): RedirectResponse
    {
        if (ENVIRONMENT !== 'development') {
            return redirect()->to('/auth/login')->with('error', 'Operação não permitida.');
        }
        $email = strtolower(trim((string) ($this->request->getPost('email') ?? '')));
        $newPass = (string) ($this->request->getPost('new_password') ?? '12345678');
        if ($email === '' || strlen($newPass) < 8) {
            return redirect()->back()->with('error', 'Dados inválidos.');
        }
        $user = $this->userModel->where('email', $email)->withDeleted()->first();
        if (!$user) {
            return redirect()->back()->with('error', 'Usuário não encontrado.');
        }
        try {
            $this->userModel->update((int)$user['id'], ['password' => password_hash($newPass, PASSWORD_DEFAULT), 'deleted_at' => null, 'is_active' => 1]);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Falha ao atualizar senha.');
        }
        return redirect()->back()->with('success', 'Senha atualizada para ambiente de desenvolvimento.');
    }

    /**
     * Recuperação de senha: gera senha temporária e envia por e-mail
     */
    public function forgot(): RedirectResponse
    {
        $emailInput = trim((string) ($this->request->getPost('email') ?? ''));
        if ($emailInput === '' || !filter_var($emailInput, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', 'Informe um e-mail válido.');
        }
        $user = $this->userModel->where('email', strtolower($emailInput))->first();
        if (!$user) {
            return redirect()->back()->with('error', 'E-mail não encontrado.');
        }
        $tempPass = bin2hex(random_bytes(5));
        $hash = password_hash($tempPass, PASSWORD_DEFAULT);
        try {
            $this->userModel->update((int)$user['id'], ['password' => $hash]);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Falha ao gerar nova senha.');
        }
        $mail = Services::email();
        $mail->setMailType('html');
        $mail->setTo($user['email']);
        $fromEmail = env('email.fromEmail', 'mail@rolesbr.com.br');
        $fromName  = env('email.fromName', 'RolesBR');
        $mail->setFrom($fromEmail, $fromName);
        $mail->setSubject('Recuperação de senha - RolesBR');
        $body = '<p>Olá, ' . esc($user['name'] ?? 'Usuário') . '.</p>'
              . '<p>Foi gerada uma nova senha temporária para sua conta:</p>'
              . '<p><strong>' . esc($tempPass) . '</strong></p>'
              . '<p>Use-a para entrar e, em seguida, altere sua senha no seu perfil.</p>'
              . '<p>Se não foi você quem solicitou, recomendamos alterar a senha imediatamente.</p>'
              . '<hr><small>RolesBR</small>';
        $mail->setMessage($body);
        if (!$mail->send()) {
            return redirect()->back()->with('error', 'Não foi possível enviar o e-mail de recuperação.');
        }
        return redirect()->back()->with('success', 'Senha temporária enviada para seu e-mail.');
    }

    /**
     * Reverte a impersonação (Volta para Admin)
     */
    public function revertImpersonation()
    {
        $session = session();
        if (!$session->has('original_admin_id')) {
            return redirect()->to('/dashboard');
        }

        $adminId = $session->get('original_admin_id');

        // Restore Admin Session from DB
        $admin = $this->userModel->find($adminId);
        
        if ($admin) {
            $sessionData = [
                'user_id'   => $admin['id'],
                'name'      => $admin['name'],
                'email'     => $admin['email'],
                'role'      => $admin['role'],
                'type_user' => (int) $admin['type_user'],
                'logged'    => true,
                'login_time' => time(),
            ];
            
            $session->set($sessionData);
            $session->remove('original_admin_id');
            $session->remove('original_admin_name');
            
            return redirect()->to('/dashboard/admin/estabelecimentos')->with('success', 'Sessão de administrador restaurada.');
        }

        // Fallback if admin user not found
        $session->destroy();
        return redirect()->to('/auth/login')->with('error', 'Erro ao restaurar sessão.');
    }
}
