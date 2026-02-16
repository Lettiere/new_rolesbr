<?php

namespace App\Http\Controllers;

use App\Models\Cardapio;
use App\Models\Establishment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CardapioController extends Controller
{
    public function index(Request $request)
    {
        $bares = Establishment::where('user_id', Auth::id())->orderBy('nome')->get(['bares_id','nome']);
        $q = trim((string)$request->get('q'));
        $b = $request->get('bares_id');
        $rows = Cardapio::when($q, fn($qr)=>$qr->where('nome','like',"%$q%"))
            ->when($b, fn($qr)=>$qr->where('bares_id',$b))
            ->orderBy('nome')->paginate(20);
        return view('dashboard.barista.products.cardapios.index', compact('rows','q','bares','b'));
    }

    public function create()
    {
        $bares = Establishment::where('user_id', Auth::id())->orderBy('nome')->get(['bares_id','nome']);
        return view('dashboard.barista.products.cardapios.create', compact('bares'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bares_id' => 'required|exists:form_perfil_bares_tb,bares_id',
            'nome' => 'required|string|max:150',
            'descricao' => 'nullable|string',
            'tipo_cardapio' => 'required|in:local,delivery,rodizio,cafeteria,padaria,pizzaria,outro',
            'status' => 'nullable|in:ativo,inativo',
        ]);
        $data['status'] = $data['status'] ?? 'ativo';
        Cardapio::create($data);
        return redirect()->route('dashboard.barista.cardapios.index')->with('success','Cardápio criado');
    }

    public function edit($id)
    {
        $row = Cardapio::findOrFail($id);
        $bares = Establishment::where('user_id', Auth::id())->orderBy('nome')->get(['bares_id','nome']);
        return view('dashboard.barista.products.cardapios.edit', compact('row','bares'));
    }

    public function update(Request $request, $id)
    {
        $row = Cardapio::findOrFail($id);
        $data = $request->validate([
            'bares_id' => 'required|exists:form_perfil_bares_tb,bares_id',
            'nome' => 'required|string|max:150',
            'descricao' => 'nullable|string',
            'tipo_cardapio' => 'required|in:local,delivery,rodizio,cafeteria,padaria,pizzaria,outro',
            'status' => 'nullable|in:ativo,inativo',
        ]);
        $data['status'] = $data['status'] ?? 'ativo';
        $row->update($data);
        return redirect()->route('dashboard.barista.cardapios.index')->with('success','Cardápio atualizado');
    }

    public function destroy($id)
    {
        $row = Cardapio::findOrFail($id);
        $row->update(['status'=>'inativo']);
        return redirect()->route('dashboard.barista.cardapios.index')->with('success','Cardápio inativado');
    }
}
