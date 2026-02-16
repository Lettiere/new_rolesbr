<?php

namespace App\Controllers\DashClient\Perfil;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use App\Models\Perfil\PerfilBarClientModel;

class DashPperfilController extends BaseController
{
    public function index()
    {
        helper('text'); // Carrega o helper de texto para usar character_limiter na view

        $session = session();
        $userId = $session->get('user_id');

        // Carregar bares do usuário
        $model = new PerfilBarClientModel();
        $bares = $model->where('user_id', $userId)->findAll();

        return view('dash_client/perfil/perfil_client.php', [
            'bares' => $bares
        ]);
    }

    // Novo método para carregar formulário específico via AJAX e retornar JSON
    public function get_form()
    {
        $tipo = $this->request->getPost('tipo');
        $html = '';

        if ($tipo == 'bares') {
            $html = view('dash_client/forms_perfil/forms_perfis_bar');
        } 
        elseif ($tipo == 'baladas') {
            $html = view('dash_client/forms_perfil/forms_perfis_baladas');
        }
        else {
            $html = '<p>Formulário não disponível.</p>';
        }

        // Retorna JSON com o HTML e o novo token CSRF
        return $this->response->setJSON([
            'html' => $html,
            'csrf_token' => csrf_hash() // Retorna o hash atual (ou novo se regenerado)
        ]);
    }
}
