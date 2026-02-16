<?php

namespace App\Http\Controllers;

use App\Models\ProductFamily;
use Illuminate\Http\Request;

class ProductFamilyController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q'));
        $rows = ProductFamily::when($q, fn($qr)=>$qr->where('nome','like',"%$q%"))
            ->orderBy('nome')->paginate(20);
        return view('dashboard.barista.products.families.index', compact('rows','q'));
    }

    public function create()
    {
        return view('dashboard.barista.products.families.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:100',
            'descricao' => 'nullable|string',
            'ativo' => 'nullable|boolean',
        ]);
        $data['ativo'] = $request->boolean('ativo', true);
        ProductFamily::create($data);
        return redirect()->route('dashboard.barista.products.families.index')->with('success','Família criada');
    }

    public function edit($id)
    {
        $row = ProductFamily::findOrFail($id);
        return view('dashboard.barista.products.families.edit', compact('row'));
    }

    public function update(Request $request, $id)
    {
        $row = ProductFamily::findOrFail($id);
        $data = $request->validate([
            'nome' => 'required|string|max:100',
            'descricao' => 'nullable|string',
            'ativo' => 'nullable|boolean',
        ]);
        $data['ativo'] = $request->boolean('ativo', true);
        $row->update($data);
        return redirect()->route('dashboard.barista.products.families.index')->with('success','Família atualizada');
    }

    public function destroy($id)
    {
        $row = ProductFamily::findOrFail($id);
        $row->update(['ativo'=>0]);
        return redirect()->route('dashboard.barista.products.families.index')->with('success','Família inativada');
    }
}
