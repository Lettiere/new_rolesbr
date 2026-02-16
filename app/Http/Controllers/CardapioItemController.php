<?php

namespace App\Http\Controllers;

use App\Models\CardapioItem;
use App\Models\Cardapio;
use App\Models\Product;
use Illuminate\Http\Request;

class CardapioItemController extends Controller
{
    public function index(Request $request)
    {
        $cardapioId = $request->get('cardapio_id');
        $rows = CardapioItem::with(['cardapio'])
            ->when($cardapioId, fn($qr)=>$qr->where('cardapio_id',$cardapioId))
            ->orderBy('ordem')->paginate(20);
        $cardapios = Cardapio::orderBy('nome')->get(['cardapio_id','nome']);
        return view('dashboard.barista.products.cardapios.items.index', compact('rows','cardapios','cardapioId'));
    }

    public function create()
    {
        $cardapios = Cardapio::orderBy('nome')->get(['cardapio_id','nome']);
        $products = Product::orderBy('nome')->get(['prod_id','nome']);
        return view('dashboard.barista.products.cardapios.items.create', compact('cardapios','products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cardapio_id' => 'required|exists:prod_cardapio_tb,cardapio_id',
            'prod_id' => 'required|exists:prod_produtos_tb,prod_id',
            'categoria' => 'nullable|string|max:100',
            'ordem' => 'nullable|integer',
            'preco_override' => 'nullable|numeric',
            'observacoes' => 'nullable|string|max:255',
        ]);
        $data['ordem'] = $data['ordem'] ?? 0;
        CardapioItem::create($data);
        return redirect()->route('dashboard.barista.cardapios.items.index',['cardapio_id'=>$data['cardapio_id']])->with('success','Item adicionado');
    }

    public function edit($id)
    {
        $row = CardapioItem::findOrFail($id);
        $cardapios = Cardapio::orderBy('nome')->get(['cardapio_id','nome']);
        $products = Product::orderBy('nome')->get(['prod_id','nome']);
        return view('dashboard.barista.products.cardapios.items.edit', compact('row','cardapios','products'));
    }

    public function update(Request $request, $id)
    {
        $row = CardapioItem::findOrFail($id);
        $data = $request->validate([
            'cardapio_id' => 'required|exists:prod_cardapio_tb,cardapio_id',
            'prod_id' => 'required|exists:prod_produtos_tb,prod_id',
            'categoria' => 'nullable|string|max:100',
            'ordem' => 'nullable|integer',
            'preco_override' => 'nullable|numeric',
            'observacoes' => 'nullable|string|max:255',
        ]);
        $data['ordem'] = $data['ordem'] ?? 0;
        $row->update($data);
        return redirect()->route('dashboard.barista.cardapios.items.index',['cardapio_id'=>$data['cardapio_id']])->with('success','Item atualizado');
    }

    public function destroy($id)
    {
        $row = CardapioItem::findOrFail($id);
        $cid = $row->cardapio_id;
        $row->delete();
        return redirect()->route('dashboard.barista.cardapios.items.index',['cardapio_id'=>$cid])->with('success','Item removido');
    }
}
