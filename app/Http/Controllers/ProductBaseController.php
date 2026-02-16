<?php

namespace App\Http\Controllers;

use App\Models\ProductBase;
use App\Models\ProductType;
use Illuminate\Http\Request;

class ProductBaseController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q'));
        $rows = ProductBase::with('type')
            ->when($q, fn($qr)=>$qr->where('nome','like',"%$q%"))
            ->orderBy('nome')->paginate(20);
        return view('dashboard.barista.products.bases.index', compact('rows','q'));
    }

    public function create()
    {
        $types = ProductType::where('ativo',1)->orderBy('nome')->get(['tipo_id','nome']);
        return view('dashboard.barista.products.bases.create', compact('types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo_id' => 'required|exists:prod_tipo_produtos_tb,tipo_id',
            'nome' => 'required|string|max:150',
            'descricao' => 'nullable|string',
            'caracteristica' => 'nullable|string|max:150',
            'unidade_padrao' => 'nullable|string|max:50',
            'tags' => 'nullable|string',
            'ativo' => 'nullable|boolean',
        ]);
        $data['ativo'] = $request->boolean('ativo', true);
        ProductBase::create($data);
        return redirect()->route('dashboard.barista.products.bases.index')->with('success','Base criada');
    }

    public function edit($id)
    {
        $row = ProductBase::findOrFail($id);
        $types = ProductType::where('ativo',1)->orderBy('nome')->get(['tipo_id','nome']);
        return view('dashboard.barista.products.bases.edit', compact('row','types'));
    }

    public function update(Request $request, $id)
    {
        $row = ProductBase::findOrFail($id);
        $data = $request->validate([
            'tipo_id' => 'required|exists:prod_tipo_produtos_tb,tipo_id',
            'nome' => 'required|string|max:150',
            'descricao' => 'nullable|string',
            'caracteristica' => 'nullable|string|max:150',
            'unidade_padrao' => 'nullable|string|max:50',
            'tags' => 'nullable|string',
            'ativo' => 'nullable|boolean',
        ]);
        $data['ativo'] = $request->boolean('ativo', true);
        $row->update($data);
        return redirect()->route('dashboard.barista.products.bases.index')->with('success','Base atualizada');
    }

    public function destroy($id)
    {
        $row = ProductBase::findOrFail($id);
        $row->update(['ativo'=>0]);
        return redirect()->route('dashboard.barista.products.bases.index')->with('success','Base inativada');
    }
}
