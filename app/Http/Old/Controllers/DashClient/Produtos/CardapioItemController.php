<?php

namespace App\Controllers\DashClient\Produtos;

use App\Controllers\BaseController;
use App\Models\Produtos\CardapioModel;
use App\Models\Produtos\CardapioItemModel;
use App\Models\Produtos\ProdutoModel;
use App\Models\Perfil\PerfilBarClientModel;

class CardapioItemController extends BaseController
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

    public function create($barId, $cardapioId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $cardapio = $this->cardapioModel->find($cardapioId);
        if (!$cardapio || $cardapio['bares_id'] != $barId) {
            return redirect()->back()->with('error', 'Cardápio não encontrado.');
        }

        // Buscar todos os produtos do bar para o modal de seleção
        $produtos = $this->produtoModel->where('bares_id', $barId)->where('status', 'ativo')->findAll();

        return view('dash_client/cardapios_itens/cadastro', [
            'bar_id' => $barId,
            'cardapio' => $cardapio,
            'produtos' => $produtos
        ]);
    }

    public function store($barId, $cardapioId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $itens = $this->request->getPost('itens'); // Array de itens

        if (empty($itens)) {
            return redirect()->back()->with('error', 'Nenhum item selecionado.');
        }

        $count = 0;
        foreach ($itens as $item) {
            $data = [
                'cardapio_id' => $cardapioId,
                'prod_id' => $item['prod_id'],
                'categoria' => $item['categoria'] ?? null, // Pode vir do produto ou input
                'preco_override' => !empty($item['preco']) ? str_replace(',', '.', $item['preco']) : null,
                'observacoes' => $item['observacoes'] ?? null,
                'ordem' => 0 // Pode implementar ordenação depois
            ];
            
            $this->itemModel->insert($data);
            $count++;
        }

        return redirect()->to("/dashboard/perfil/bar/{$barId}/cardapios/show/{$cardapioId}")->with('success', "{$count} itens adicionados ao cardápio.");
    }

    public function delete($barId, $cardapioId, $itemId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $this->itemModel->delete($itemId);

        return redirect()->back()->with('success', 'Item removido.');
    }
}
