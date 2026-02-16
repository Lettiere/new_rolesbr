<?php

namespace App\Controllers\DashClient\Produtos;

use App\Controllers\BaseController;
use App\Models\Produtos\CardapioModel;
use App\Models\Produtos\CardapioItemModel;
use App\Models\Produtos\ProdutoModel;
use App\Models\Perfil\PerfilBarClientModel;

class CardapioController extends BaseController
{
    protected $cardapioModel;
    protected $itemModel;
    protected $produtoModel;
    protected $barModel;

    public function __construct()
    {
        $this->cardapioModel = new CardapioModel();
        $this->itemModel = new CardapioItemModel();
        $this->produtoModel = new ProdutoModel();
        $this->barModel = new PerfilBarClientModel();
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

        $cardapios = $this->cardapioModel->where('bares_id', $barId)->findAll();

        return view('dash_client/cardapios/index', [
            'bar_id' => $barId,
            'cardapios' => $cardapios
        ]);
    }

    public function create($barId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        return view('dash_client/cardapios/cadastro', ['bar_id' => $barId]);
    }

    public function store($barId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $post = $this->request->getPost();

        $data = [
            'bares_id' => $barId,
            'nome' => $post['nome'],
            'descricao' => $post['descricao'],
            'tipo_cardapio' => $post['tipo_cardapio'],
            'status' => 'ativo'
        ];

        $this->cardapioModel->insert($data);

        return redirect()->to("/dashboard/perfil/bar/{$barId}/cardapios")->with('success', 'Cardápio criado.');
    }

    public function edit($barId, $cardapioId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $cardapio = $this->cardapioModel->find($cardapioId);
        if (!$cardapio || $cardapio['bares_id'] != $barId) {
            return redirect()->back()->with('error', 'Cardápio não encontrado.');
        }

        return view('dash_client/cardapios/edit', ['bar_id' => $barId, 'cardapio' => $cardapio]);
    }

    public function update($barId, $cardapioId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $data = [
            'nome' => $this->request->getPost('nome'),
            'descricao' => $this->request->getPost('descricao'),
            'tipo_cardapio' => $this->request->getPost('tipo_cardapio'),
        ];

        $this->cardapioModel->update($cardapioId, $data);

        return redirect()->to("/dashboard/perfil/bar/{$barId}/cardapios")->with('success', 'Cardápio atualizado.');
    }
    
    public function show($barId, $cardapioId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }
        
        $cardapio = $this->cardapioModel->find($cardapioId);
        
        // Buscar itens do cardápio com join em produtos para pegar o nome
        $itens = $this->itemModel
            ->select('prod_cardapio_itens_tb.*, prod_produtos_tb.nome as produto_nome, prod_produtos_tb.unidade, prod_produtos_tb.preco as produto_preco')
            ->join('prod_produtos_tb', 'prod_produtos_tb.prod_id = prod_cardapio_itens_tb.prod_id')
            ->where('cardapio_id', $cardapioId)
            ->orderBy('ordem', 'ASC')
            ->findAll();
        
        return view('dash_client/cardapios/show', [
            'bar_id' => $barId, 
            'cardapio' => $cardapio,
            'itens' => $itens
        ]);
    }
    
    public function delete($barId, $cardapioId)
    {
         if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }
        
        $cardapio = $this->cardapioModel->find($cardapioId);
        if (!$cardapio || $cardapio['bares_id'] != $barId) {
            return redirect()->back()->with('error', 'Cardápio não encontrado.');
        }
        
        $this->cardapioModel->delete($cardapioId);
        
        return redirect()->to("/dashboard/perfil/bar/{$barId}/cardapios")->with('success', 'Cardápio removido.');
    }
}
