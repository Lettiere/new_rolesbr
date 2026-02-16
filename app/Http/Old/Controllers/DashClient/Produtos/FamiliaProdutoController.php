<?php

namespace App\Controllers\DashClient\Produtos;

use App\Controllers\BaseController;
use App\Models\Produtos\FamiliaProdutoModel;

class FamiliaProdutoController extends BaseController
{
    protected $familiaModel;

    public function __construct()
    {
        $this->familiaModel = new FamiliaProdutoModel();
    }

    public function index()
    {
        $familias = $this->familiaModel->findAll();
        return view('dash_client/produtos/familia/index', ['familias' => $familias]);
    }

    public function create()
    {
        return view('dash_client/produtos/familia/cadastro');
    }

    public function store()
    {
        $data = [
            'nome' => $this->request->getPost('nome'),
            'descricao' => $this->request->getPost('descricao'),
            'ativo' => $this->request->getPost('ativo') ? 1 : 0
        ];

        if ($this->familiaModel->insert($data)) {
            return redirect()->to('/dashboard/config/produtos/familia')->with('success', 'Família criada com sucesso.');
        }

        return redirect()->back()->withInput()->with('error', 'Erro ao criar família.');
    }

    public function edit($id)
    {
        $familia = $this->familiaModel->find($id);
        if (!$familia) {
            return redirect()->back()->with('error', 'Família não encontrada.');
        }
        return view('dash_client/produtos/familia/edit', ['familia' => $familia]);
    }

    public function update($id)
    {
        $data = [
            'nome' => $this->request->getPost('nome'),
            'descricao' => $this->request->getPost('descricao'),
            'ativo' => $this->request->getPost('ativo') ? 1 : 0
        ];

        if ($this->familiaModel->update($id, $data)) {
            return redirect()->to('/dashboard/config/produtos/familia')->with('success', 'Família atualizada com sucesso.');
        }

        return redirect()->back()->withInput()->with('error', 'Erro ao atualizar família.');
    }

    public function show($id)
    {
        $familia = $this->familiaModel->find($id);
        if (!$familia) {
            return redirect()->back()->with('error', 'Família não encontrada.');
        }
        return view('dash_client/produtos/familia/show', ['familia' => $familia]);
    }
}
