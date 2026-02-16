<?php

namespace App\Controllers\DashClient\Produtos;

use App\Controllers\BaseController;
use App\Models\Produtos\TipoProdutoModel;
use App\Models\Produtos\FamiliaProdutoModel;

class TipoProdutoController extends BaseController
{
    protected $tipoModel;
    protected $familiaModel;

    public function __construct()
    {
        $this->tipoModel = new TipoProdutoModel();
        $this->familiaModel = new FamiliaProdutoModel();
    }

    public function index()
    {
        $tipos = $this->tipoModel->findAll();
        // Carregar nome da família para cada tipo
        foreach ($tipos as &$tipo) {
            $familia = $this->familiaModel->find($tipo['familia_id']);
            $tipo['familia_nome'] = $familia ? $familia['nome'] : 'Desconhecida';
        }
        return view('dash_client/produtos/tipo/index', ['tipos' => $tipos]);
    }

    public function create()
    {
        $familias = $this->familiaModel->where('ativo', 1)->findAll();
        return view('dash_client/produtos/tipo/cadastro', ['familias' => $familias]);
    }

    public function store()
    {
        $data = [
            'familia_id' => $this->request->getPost('familia_id'),
            'nome' => $this->request->getPost('nome'),
            'descricao' => $this->request->getPost('descricao'),
            'ativo' => $this->request->getPost('ativo') ? 1 : 0
        ];

        if ($this->tipoModel->insert($data)) {
            return redirect()->to('/dashboard/config/produtos/tipo')->with('success', 'Tipo criado com sucesso.');
        }

        return redirect()->back()->withInput()->with('error', 'Erro ao criar tipo.');
    }

    public function edit($id)
    {
        $tipo = $this->tipoModel->find($id);
        if (!$tipo) {
            return redirect()->back()->with('error', 'Tipo não encontrado.');
        }
        $familias = $this->familiaModel->where('ativo', 1)->findAll();
        return view('dash_client/produtos/tipo/edit', ['tipo' => $tipo, 'familias' => $familias]);
    }

    public function update($id)
    {
        $data = [
            'familia_id' => $this->request->getPost('familia_id'),
            'nome' => $this->request->getPost('nome'),
            'descricao' => $this->request->getPost('descricao'),
            'ativo' => $this->request->getPost('ativo') ? 1 : 0
        ];

        if ($this->tipoModel->update($id, $data)) {
            return redirect()->to('/dashboard/config/produtos/tipo')->with('success', 'Tipo atualizado com sucesso.');
        }

        return redirect()->back()->withInput()->with('error', 'Erro ao atualizar tipo.');
    }

    public function show($id)
    {
        $tipo = $this->tipoModel->find($id);
        if (!$tipo) {
            return redirect()->back()->with('error', 'Tipo não encontrado.');
        }
        $familia = $this->familiaModel->find($tipo['familia_id']);
        $tipo['familia_nome'] = $familia ? $familia['nome'] : 'Desconhecida';
        return view('dash_client/produtos/tipo/show', ['tipo' => $tipo]);
    }
}
