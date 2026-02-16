<?php

namespace App\Http\Controllers;

use App\Models\AttractionStyle;
use Illuminate\Http\Request;

class AttractionStyleController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $rows = AttractionStyle::when($q, fn($qq)=>$qq->where('nome','like',"%$q%"))
            ->orderBy('nome')->paginate(20);
        return view('dashboard.barista.events.styles', compact('rows','q'));
    }

    public function create()
    {
        return view('dashboard.barista.events.styles.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:120',
            'categoria' => 'nullable|string|max:120',
            'descricao' => 'nullable|string',
            'ativo' => 'nullable|boolean',
        ]);
        $data['ativo'] = $request->boolean('ativo', true);
        AttractionStyle::create($data);
        return redirect()->route('dashboard.barista.events.styles')->with('ok','Estilo criado');
    }

    public function edit($id)
    {
        $row = AttractionStyle::findOrFail($id);
        return view('dashboard.barista.events.styles.edit', compact('row'));
    }

    public function update(Request $request, $id)
    {
        $row = AttractionStyle::findOrFail($id);
        $data = $request->validate([
            'nome' => 'required|string|max:120',
            'categoria' => 'nullable|string|max:120',
            'descricao' => 'nullable|string',
            'ativo' => 'nullable|boolean',
        ]);
        $data['ativo'] = $request->boolean('ativo', true);
        $row->update($data);
        return redirect()->route('dashboard.barista.events.styles')->with('ok','Estilo atualizado');
    }

    public function destroy($id)
    {
        $row = AttractionStyle::findOrFail($id);
        $row->update(['ativo'=>0]);
        return redirect()->route('dashboard.barista.events.styles')->with('ok','Estilo inativado');
    }
}
