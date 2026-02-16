<?php

namespace App\Http\Controllers;

use App\Models\EventType;
use Illuminate\Http\Request;

class EventTypeController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $rows = EventType::when($q, fn($qq)=>$qq->where('nome','like',"%$q%"))
            ->orderBy('nome')->paginate(20);
        return view('dashboard.barista.events.types', compact('rows','q'));
    }

    public function create()
    {
        return view('dashboard.barista.events.types.create');
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
        EventType::create($data);
        return redirect()->route('dashboard.barista.events.types')->with('ok','Tipo criado');
    }

    public function storeAjax(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:120',
            'categoria' => 'nullable|string|max:120',
            'descricao' => 'nullable|string',
            'ativo' => 'nullable|boolean',
        ]);
        $data['ativo'] = $request->boolean('ativo', true);
        $row = EventType::create($data);
        return response()->json($row);
    }

    public function edit($id)
    {
        $row = EventType::findOrFail($id);
        return view('dashboard.barista.events.types.edit', compact('row'));
    }

    public function update(Request $request, $id)
    {
        $row = EventType::findOrFail($id);
        $data = $request->validate([
            'nome' => 'required|string|max:120',
            'categoria' => 'nullable|string|max:120',
            'descricao' => 'nullable|string',
            'ativo' => 'nullable|boolean',
        ]);
        $data['ativo'] = $request->boolean('ativo', true);
        $row->update($data);
        return redirect()->route('dashboard.barista.events.types')->with('ok','Tipo atualizado');
    }

    public function destroy($id)
    {
        $row = EventType::findOrFail($id);
        $row->update(['ativo'=>0]);
        return redirect()->route('dashboard.barista.events.types')->with('ok','Tipo inativado');
    }
}
