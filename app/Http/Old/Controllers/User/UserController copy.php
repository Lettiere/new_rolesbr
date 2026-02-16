<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\User\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class UserController extends BaseController
{
public function store()
{
    $userModel = new UserModel();

    // Recupera os dados do form
    $data = [
        'name'      => $this->request->getPost('name'),
        'email'     => $this->request->getPost('email'),
        'password'  => $this->request->getPost('password'),
        'role'      => 'user', // valor padrão, pode-se ajustar futuramente
        'type_user' => $this->request->getPost('type_user'),
        'is_active' => 1
    ];

    // Confirmação de senha
    $passwordConfirm = $this->request->getPost('password_confirm');

    // Validação customizada: senhas iguais
    if($data['password'] !== $passwordConfirm) {
        return redirect()->back()->withInput()->with('error', 'As senhas não conferem!');
    }

    // Hash da senha
    $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

    // Executa a validação (usando validationRules do model)
    if (!$userModel->validate($data)) {
        return redirect()->back()->withInput()->with('error', implode(' ', $userModel->errors()));
    }

    // Confere duplicidade de email
    if ($userModel->where('email', $data['email'])->first()) {
        return redirect()->back()->withInput()->with('error', 'E-mail já está cadastrado.');
    }

    // Insere no banco
    if ($userModel->insert($data)) {
        // (Opcional: Realizar login automático após cadastro)
        // session()->set(['user_id' => $userModel->getInsertID()]);
        return redirect()->to('/')->with('success', 'Usuário cadastrado com sucesso! Agora você pode acessar o sistema.');
    } else {
        return redirect()->back()->withInput()->with('error', 'Erro ao cadastrar. Tente novamente.');
    }
}
}
