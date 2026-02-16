<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventTicketLot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EventTicketLotController extends Controller
{
    public function index($eventoId)
    {
        $event = Event::whereHas('establishment', function($q){ $q->where('user_id', Auth::id()); })->findOrFail($eventoId);
        $lots = EventTicketLot::where('evento_id', $eventoId)->orderBy('preco','asc')->paginate(20);
        $soldByLot = DB::table('evt_ingressos_vendidos_tb')
            ->select('lote_id', DB::raw('COUNT(*) as vendidos'))
            ->where('evento_id', $eventoId)
            ->groupBy('lote_id')
            ->pluck('vendidos','lote_id')
            ->toArray();
        $lots->getCollection()->transform(function($l) use ($soldByLot){
            $l->quantidade_vendida = $soldByLot[$l->lote_id] ?? 0;
            return $l;
        });
        return view('dashboard.barista.events.tickets.lots.index', compact('event','lots'));
    }

    public function create($eventoId)
    {
        $event = Event::whereHas('establishment', function($q){ $q->where('user_id', Auth::id()); })->findOrFail($eventoId);
        return view('dashboard.barista.events.tickets.lots.create', compact('event'));
    }

    public function store(Request $request, $eventoId)
    {
        $event = Event::whereHas('establishment', function($q){ $q->where('user_id', Auth::id()); })->findOrFail($eventoId);
        $data = $request->validate([
            'nome' => 'required|string|max:120',
            'tipo' => 'required|in:gratis,desconto,inteira,meia,outro',
            'preco' => 'nullable|numeric|min:0',
            'quantidade_total' => 'required|integer|min:0',
            'data_inicio_vendas' => 'nullable|date',
            'data_fim_vendas' => 'nullable|date|after_or_equal:data_inicio_vendas',
            'ativo' => 'nullable|boolean',
            'status' => 'nullable|string|max:30',
        ]);
        $data['evento_id'] = $event->evento_id;
        $data['ativo'] = $request->boolean('ativo', true);
        EventTicketLot::create($data);
        return redirect()->route('dashboard.barista.events.lots.index', $event->evento_id)->with('ok','Lote criado');
    }

    public function edit($eventoId, $id)
    {
        $event = Event::whereHas('establishment', function($q){ $q->where('user_id', Auth::id()); })->findOrFail($eventoId);
        $lot = EventTicketLot::where('evento_id',$eventoId)->findOrFail($id);
        return view('dashboard.barista.events.tickets.lots.edit', compact('event','lot'));
    }

    public function update(Request $request, $eventoId, $id)
    {
        $event = Event::whereHas('establishment', function($q){ $q->where('user_id', Auth::id()); })->findOrFail($eventoId);
        $lot = EventTicketLot::where('evento_id',$eventoId)->findOrFail($id);
        $data = $request->validate([
            'nome' => 'required|string|max:120',
            'tipo' => 'required|in:gratis,desconto,inteira,meia,outro',
            'preco' => 'nullable|numeric|min:0',
            'quantidade_total' => 'required|integer|min:0',
            'data_inicio_vendas' => 'nullable|date',
            'data_fim_vendas' => 'nullable|date|after_or_equal:data_inicio_vendas',
            'ativo' => 'nullable|boolean',
            'status' => 'nullable|string|max:30',
        ]);
        $data['ativo'] = $request->boolean('ativo', true);
        $lot->update($data);
        return redirect()->route('dashboard.barista.events.lots.index', $event->evento_id)->with('ok','Lote atualizado');
    }

    public function destroy($eventoId, $id)
    {
        $event = Event::whereHas('establishment', function($q){ $q->where('user_id', Auth::id()); })->findOrFail($eventoId);
        $lot = EventTicketLot::where('evento_id',$eventoId)->findOrFail($id);
        $lot->update(['ativo'=>0,'status'=>'inativo']);
        return redirect()->route('dashboard.barista.events.lots.index', $event->evento_id)->with('ok','Lote inativado');
    }

    public function buyers(Request $request, $eventoId)
    {
        $event = Event::whereHas('establishment', function($q){ $q->where('user_id', Auth::id()); })->findOrFail($eventoId);
        $lots = EventTicketLot::where('evento_id', $eventoId)
            ->orderBy('preco', 'asc')
            ->get(['lote_id', 'nome']);
        $selectedLot = $request->input('lote_id');
        $query = DB::table('evt_ingressos_vendidos_tb as iv')
            ->select([
                'iv.ingresso_id',
                'iv.codigo_unico',
                'iv.status',
                'iv.valor_pago',
                'iv.data_compra',
                'iv.nome_comprador',
                'iv.email_comprador',
                'iv.lote_id',
                'l.nome as lote_nome',
                'users.name as user_name',
                'pu.cpf',
            ])
            ->join('evt_lotes_ingressos_tb as l', 'l.lote_id', '=', 'iv.lote_id')
            ->join('users', 'users.id', '=', 'iv.user_id')
            ->leftJoin('perfil_usuarios_tb as pu', 'pu.user_id', '=', 'iv.user_id')
            ->where('iv.evento_id', $eventoId);
        if ($selectedLot) {
            $query->where('iv.lote_id', $selectedLot);
        }
        $sales = $query->orderBy('iv.data_compra', 'desc')
            ->paginate(20)
            ->appends($request->query());
        return view('dashboard.barista.events.tickets.lots.buyers', compact('event', 'lots', 'sales', 'selectedLot'));
    }
}
