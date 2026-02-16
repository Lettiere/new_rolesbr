<?php

namespace App\Http\Controllers;

use App\Models\ProductType;
use App\Models\ProductFamily;
use Illuminate\Http\Request;

class ProductTypeController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q'));
        $rows = ProductType::with('bases')
            ->when($q, fn($qr)=>$qr->where('nome','like',"%$q%"))
            ->orderBy('nome')->paginate(20);
        return view('dashboard.barista.products.types.index', compact('rows','q'));
    }

    public function create()
    {
        $families = ProductFamily::where('ativo',1)->orderBy('nome')->get(['familia_id','nome']);
        return view('dashboard.barista.products.types.create', compact('families'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'familia_id' => 'required|exists:prod_familia_produtos_tb,familia_id',
            'nome' => 'required|string|max:100',
            'descricao' => 'nullable|string',
            'ativo' => 'nullable|boolean',
        ]);
        $data['ativo'] = $request->boolean('ativo', true);
        ProductType::create($data);
        return redirect()->route('dashboard.barista.products.types.index')->with('success','Tipo criado');
    }

    public function edit($id)
    {
        $row = ProductType::findOrFail($id);
        $families = ProductFamily::where('ativo',1)->orderBy('nome')->get(['familia_id','nome']);
        return view('dashboard.barista.products.types.edit', compact('row','families'));
    }

    public function update(Request $request, $id)
    {
        $row = ProductType::findOrFail($id);
        $data = $request->validate([
            'familia_id' => 'required|exists:prod_familia_produtos_tb,familia_id',
            'nome' => 'required|string|max:100',
            'descricao' => 'nullable|string',
            'ativo' => 'nullable|boolean',
        ]);
        $data['ativo'] = $request->boolean('ativo', true);
        $row->update($data);
        return redirect()->route('dashboard.barista.products.types.index')->with('success','Tipo atualizado');
    }

    public function destroy($id)
    {
        $row = ProductType::findOrFail($id);
        $row->update(['ativo'=>0]);
        return redirect()->route('dashboard.barista.products.types.index')->with('success','Tipo inativado');
    }
}
