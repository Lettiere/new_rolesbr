<?php

namespace App\Controllers\DashClient\Produtos;

use App\Controllers\BaseController;
use App\Models\Produtos\ProdutoModel;
use App\Models\Produtos\FotoProdutoModel;
use App\Models\Perfil\PerfilBarClientModel;
use App\Models\Produtos\BaseProdutoModel;
use App\Models\Produtos\TipoProdutoModel;
use App\Models\Produtos\FamiliaProdutoModel;

class ProdutoController extends BaseController
{
    protected $produtoModel;
    protected $fotoModel;
    protected $barModel;
    protected $baseModel;
    protected $tipoModel;
    protected $familiaModel;

    public function __construct()
    {
        $this->produtoModel = new ProdutoModel();
        $this->fotoModel = new FotoProdutoModel();
        $this->barModel = new PerfilBarClientModel();
        $this->baseModel = new BaseProdutoModel();
        $this->tipoModel = new TipoProdutoModel();
        $this->familiaModel = new FamiliaProdutoModel();
    }

    private function checkPermission($barId)
    {
        $userId = session()->get('user_id');
        $bar = $this->barModel->where('user_id', $userId)->find($barId);
        return $bar;
    }

    public function index($barId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $produtos = $this->produtoModel->where('bares_id', $barId)->findAll();
        
        // Carregar a primeira foto de cada produto
        foreach ($produtos as &$prod) {
            $foto = $this->fotoModel->where('prod_id', $prod['prod_id'])->orderBy('ordem', 'ASC')->first();
            $prod['imagem'] = $foto ? $foto['url'] : null;
        }

        $data = [
            'bar_id' => $barId,
            'produtos' => $produtos
        ];

        return view('dash_client/produtos/index', $data);
    }

    public function create($barId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $bases = $this->baseModel->where('ativo', 1)->findAll();
        $tipos = $this->tipoModel->where('ativo', 1)->findAll();
        $familias = $this->familiaModel->where('ativo', 1)->findAll();

        return view('dash_client/produtos/cadastro', [
            'bar_id' => $barId,
            'bases' => $bases,
            'tipos' => $tipos,
            'familias' => $familias
        ]);
    }

    public function store($barId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $post = $this->request->getPost();

        // Se base_id for enviado, busca dados padrão
        $baseId = !empty($post['base_id']) ? $post['base_id'] : null;
        $familiaId = !empty($post['familia_id']) ? $post['familia_id'] : null;
        $tipoId = !empty($post['tipo_id']) ? $post['tipo_id'] : null;
        
        $data = [
            'bares_id' => $barId,
            'base_id' => $baseId,
            'familia_id' => $familiaId,
            'tipo_id' => $tipoId,
            'nome' => $post['nome'],
            'descricao' => $post['descricao'],
            'tipo_produto' => $post['tipo_produto'],
            'subtipo_bebida' => $post['subtipo_bebida'] ?? null,
            'preco' => str_replace(',', '.', $post['preco']),
            'quantidade_estoque' => $post['quantidade_estoque'] ?? 0,
            'unidade' => $post['unidade'],
            'tags' => !empty($post['tags']) ? $post['tags'] : null, // Recebe tags como JSON string do front
            'status' => 'ativo'
        ];

        if ($this->produtoModel->insert($data)) {
            $prodId = $this->produtoModel->getInsertID();

            // Upload de imagem
            $img = $this->request->getFile('imagem');
            if ($img && $img->isValid() && !$img->hasMoved()) {
                $path = ROOTPATH . 'public/uploads/produtos/' . $prodId;
                if (!is_dir($path)) mkdir($path, 0777, true);

                $newName = $img->getRandomName();
                $img->move($path, $newName);

                $this->fotoModel->insert([
                    'prod_id' => $prodId,
                    'url' => 'uploads/produtos/' . $prodId . '/' . $newName,
                    'descricao' => 'Foto principal',
                    'ordem' => 1
                ]);
            }

            return redirect()->to("/dashboard/perfil/bar/{$barId}/produtos")->with('success', 'Produto cadastrado com sucesso.');
        }

        return redirect()->back()->withInput()->with('error', 'Erro ao cadastrar produto.');
    }

    public function edit($barId, $prodId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $produto = $this->produtoModel->find($prodId);
        if (!$produto || $produto['bares_id'] != $barId) {
            return redirect()->back()->with('error', 'Produto não encontrado.');
        }

        $foto = $this->fotoModel->where('prod_id', $prodId)->orderBy('ordem', 'ASC')->first();
        $produto['imagem'] = $foto ? $foto['url'] : null;

        $bases = $this->baseModel->where('ativo', 1)->findAll();
        $tipos = $this->tipoModel->where('ativo', 1)->findAll();
        $familias = $this->familiaModel->where('ativo', 1)->findAll();

        return view('dash_client/produtos/edit', [
            'bar_id' => $barId, 
            'produto' => $produto,
            'bases' => $bases,
            'tipos' => $tipos,
            'familias' => $familias
        ]);
    }

    public function update($barId, $prodId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $produto = $this->produtoModel->find($prodId);
        if (!$produto || $produto['bares_id'] != $barId) {
            return redirect()->back()->with('error', 'Produto não encontrado.');
        }

        $post = $this->request->getPost();
        
        $baseId = !empty($post['base_id']) ? $post['base_id'] : null;
        $familiaId = !empty($post['familia_id']) ? $post['familia_id'] : null;
        $tipoId = !empty($post['tipo_id']) ? $post['tipo_id'] : null;

        $data = [
            'base_id' => $baseId,
            'familia_id' => $familiaId,
            'tipo_id' => $tipoId,
            'nome' => $post['nome'],
            'descricao' => $post['descricao'],
            'tipo_produto' => $post['tipo_produto'],
            'subtipo_bebida' => $post['subtipo_bebida'] ?? null,
            'preco' => str_replace(',', '.', $post['preco']),
            'quantidade_estoque' => $post['quantidade_estoque'] ?? 0,
            'unidade' => $post['unidade'],
            'tags' => !empty($post['tags']) ? $post['tags'] : null,
        ];

        $this->produtoModel->update($prodId, $data);

        // Upload de imagem (substituir ou adicionar)
        $img = $this->request->getFile('imagem');
        if ($img && $img->isValid() && !$img->hasMoved()) {
            $path = ROOTPATH . 'public/uploads/produtos/' . $prodId;
            if (!is_dir($path)) mkdir($path, 0777, true);

            $newName = $img->getRandomName();
            $img->move($path, $newName);

            // Verifica se já tem foto
            $fotoExistente = $this->fotoModel->where('prod_id', $prodId)->first();
            if ($fotoExistente) {
                // Remove arquivo antigo
                if (file_exists(ROOTPATH . 'public/' . $fotoExistente['url'])) {
                    unlink(ROOTPATH . 'public/' . $fotoExistente['url']);
                }
                $this->fotoModel->update($fotoExistente['foto_id'], ['url' => 'uploads/produtos/' . $prodId . '/' . $newName]);
            } else {
                $this->fotoModel->insert([
                    'prod_id' => $prodId,
                    'url' => 'uploads/produtos/' . $prodId . '/' . $newName,
                    'descricao' => 'Foto principal',
                    'ordem' => 1
                ]);
            }
        }

        return redirect()->to("/dashboard/perfil/bar/{$barId}/produtos")->with('success', 'Produto atualizado.');
    }

    public function show($barId, $prodId)
    {
         if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $produto = $this->produtoModel->find($prodId);
        if (!$produto || $produto['bares_id'] != $barId) {
            return redirect()->back()->with('error', 'Produto não encontrado.');
        }

        // Buscar todas as fotos
        $fotos = $this->fotoModel->where('prod_id', $prodId)->orderBy('ordem', 'ASC')->findAll();
        $produto['imagem'] = !empty($fotos) ? $fotos[0]['url'] : null;

        return view('dash_client/produtos/show', [
            'bar_id' => $barId, 
            'produto' => $produto,
            'fotos' => $fotos
        ]);
    }
    
    public function uploadFoto($barId, $prodId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $produto = $this->produtoModel->find($prodId);
        if (!$produto || $produto['bares_id'] != $barId) {
            return redirect()->back()->with('error', 'Produto não encontrado.');
        }

        $img = $this->request->getFile('foto_produto');

        if ($img && $img->isValid() && !$img->hasMoved()) {
            $path = ROOTPATH . 'public/uploads/produtos/' . $prodId;
            if (!is_dir($path)) mkdir($path, 0777, true);

            $newName = $img->getRandomName();
            $img->move($path, $newName);

            $this->fotoModel->insert([
                'prod_id' => $prodId,
                'url' => 'uploads/produtos/' . $prodId . '/' . $newName,
                'descricao' => $this->request->getPost('descricao') ?? 'Foto do produto',
                'ordem' => 0
            ]);

            return redirect()->to("/dashboard/perfil/bar/{$barId}/produtos/show/{$prodId}")->with('success', 'Foto adicionada.');
        }

        return redirect()->back()->with('error', 'Erro no upload.');
    }

    public function deleteFoto($barId, $prodId, $fotoId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $foto = $this->fotoModel->find($fotoId);
        if (!$foto || $foto['prod_id'] != $prodId) {
            return redirect()->back()->with('error', 'Foto não encontrada.');
        }

        // Remove arquivo físico
        if (file_exists(ROOTPATH . 'public/' . $foto['url'])) {
            unlink(ROOTPATH . 'public/' . $foto['url']);
        }

        $this->fotoModel->delete($fotoId);

        return redirect()->to("/dashboard/perfil/bar/{$barId}/produtos/show/{$prodId}")->with('success', 'Foto removida.');
    }

    public function delete($barId, $prodId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }
        
        $produto = $this->produtoModel->find($prodId);
        if (!$produto || $produto['bares_id'] != $barId) {
            return redirect()->back()->with('error', 'Produto não encontrado.');
        }
        
        $this->produtoModel->delete($prodId);
        
        return redirect()->to("/dashboard/perfil/bar/{$barId}/produtos")->with('success', 'Produto removido.');
    }
}
