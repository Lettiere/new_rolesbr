<?php

namespace App\Controllers\DashClient;

use App\Controllers\BaseController;

class DashClientController extends BaseController
{
    public function index()
    {
        // Aqui você pode passar variáveis para a view caso necessário
        // Exemplo: return view('dash/dash_client', ['empresa' => 'Nome da Empresa']);
        return view('dash/dash_client');
    }
}
