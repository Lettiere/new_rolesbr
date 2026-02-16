<?php

namespace App\Controllers\DashClient\Produtos;

use App\Controllers\BaseController;
use App\Models\Produtos\BaseProdutoModel;
use App\Models\Produtos\TipoProdutoModel;

class BaseProdutoController extends BaseController
{
    protected $baseModel;
    protected $tipoModel;

    public function __construct()
    {
        $this->baseModel = new BaseProdutoModel();
        $this->tipoModel = new TipoProdutoModel();
    }

    public function index()
    {
        $bases = $this->baseModel->findAll();
        // Carregar nome do tipo para cada base
        foreach ($bases as &$base) {
            $tipo = $this->tipoModel->find($base['tipo_id']);
            $base['tipo_nome'] = $tipo ? $tipo['nome'] : 'Desconhecido';
        }
        return view('dash_client/produtos/base/index', ['bases' => $bases]);
    }

    public function create()
    {
        $tipos = $this->tipoModel->where('ativo', 1)->findAll();
        return view('dash_client/produtos/base/cadastro', ['tipos' => $tipos]);
    }

    public function store()
    {
        $tags = $this->request->getPost('tags');
        // Se tags vier como string separada por virgula, converte para array JSON
        if (!empty($tags) && !is_array($tags)) {
            $tagsArray = array_map('trim', explode(',', $tags));
            $tagsJson = json_encode($tagsArray);
        } else {
            $tagsJson = null;
        }

        $data = [
            'tipo_id' => $this->request->getPost('tipo_id'),
            'nome' => $this->request->getPost('nome'),
            'descricao' => $this->request->getPost('descricao'),
            'caracteristica' => $this->request->getPost('caracteristica'),
            'unidade_padrao' => $this->request->getPost('unidade_padrao'),
            'tags' => $tagsJson,
            'ativo' => $this->request->getPost('ativo') ? 1 : 0
        ];

        if ($this->baseModel->insert($data)) {
            return redirect()->to('/dashboard/config/produtos/base')->with('success', 'Base criada com sucesso.');
        }

        return redirect()->back()->withInput()->with('error', 'Erro ao criar base.');
    }

    public function edit($id)
    {
        $base = $this->baseModel->find($id);
        if (!$base) {
            return redirect()->back()->with('error', 'Base não encontrada.');
        }
        $tipos = $this->tipoModel->where('ativo', 1)->findAll();
        return view('dash_client/produtos/base/edit', ['base' => $base, 'tipos' => $tipos]);
    }

    public function update($id)
    {
        $tags = $this->request->getPost('tags');
        if (!empty($tags) && !is_array($tags)) {
            $tagsArray = array_map('trim', explode(',', $tags));
            $tagsJson = json_encode($tagsArray);
        } else {
            $tagsJson = null;
        }

        $data = [
            'tipo_id' => $this->request->getPost('tipo_id'),
            'nome' => $this->request->getPost('nome'),
            'descricao' => $this->request->getPost('descricao'),
            'caracteristica' => $this->request->getPost('caracteristica'),
            'unidade_padrao' => $this->request->getPost('unidade_padrao'),
            'tags' => $tagsJson,
            'ativo' => $this->request->getPost('ativo') ? 1 : 0
        ];

        if ($this->baseModel->update($id, $data)) {
            return redirect()->to('/dashboard/config/produtos/base')->with('success', 'Base atualizada com sucesso.');
        }

        return redirect()->back()->withInput()->with('error', 'Erro ao atualizar base.');
    }

    public function show($id)
    {
        $base = $this->baseModel->find($id);
        if (!$base) {
            return redirect()->back()->with('error', 'Base não encontrada.');
        }
        $tipo = $this->tipoModel->find($base['tipo_id']);
        $base['tipo_nome'] = $tipo ? $tipo['nome'] : 'Desconhecido';
        return view('dash_client/produtos/base/show', ['base' => $base]);
    }
}
