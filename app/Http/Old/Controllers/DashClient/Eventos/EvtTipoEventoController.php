<?php

namespace App\Controllers\DashClient\Eventos;

use App\Controllers\BaseController;
use App\Models\Eventos\EvtTipoEventoModel;

class EvtTipoEventoController extends BaseController
{
    protected $tipoEventoModel;

    public function __construct()
    {
        $this->tipoEventoModel = new EvtTipoEventoModel();
    }

    public function index()
    {
        $tipos = $this->tipoEventoModel->findAll();
        return view('dash_client/eventos/tipos/index', ['tipos' => $tipos]);
    }

    public function create()
    {
        return view('dash_client/eventos/tipos/cadastro');
    }

    public function store()
    {
        $data = [
            'nome' => $this->request->getPost('nome'),
            'categoria' => $this->request->getPost('categoria'),
            'descricao' => $this->request->getPost('descricao'),
            'ativo' => $this->request->getPost('ativo') ? 1 : 0
        ];

        if ($this->tipoEventoModel->insert($data)) {
            return redirect()->to('/dashboard/config/eventos/tipos')->with('success', 'Tipo de evento criado com sucesso.');
        }

        return redirect()->back()->withInput()->with('error', 'Erro ao criar tipo de evento.');
    }

    public function edit($id)
    {
        $tipo = $this->tipoEventoModel->find($id);
        if (!$tipo) {
            return redirect()->back()->with('error', 'Tipo não encontrado.');
        }
        return view('dash_client/eventos/tipos/edit', ['tipo' => $tipo]);
    }

    public function update($id)
    {
        $data = [
            'nome' => $this->request->getPost('nome'),
            'categoria' => $this->request->getPost('categoria'),
            'descricao' => $this->request->getPost('descricao'),
            'ativo' => $this->request->getPost('ativo') ? 1 : 0
        ];

        if ($this->tipoEventoModel->update($id, $data)) {
            return redirect()->to('/dashboard/config/eventos/tipos')->with('success', 'Tipo atualizado com sucesso.');
        }

        return redirect()->back()->withInput()->with('error', 'Erro ao atualizar tipo.');
    }
}
