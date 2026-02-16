@extends('_layout.dashboard.barista.layout_barista')
@section('title','Lotes de Ingressos')
@section('content')
<div class="d-flex align-items-center justify-content-between pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3 m-0">Lotes de Ingressos — {{ $event->nome }}</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('dashboard.barista.events.lots.buyers',$event->evento_id) }}" class="btn btn-outline-secondary">Ver compradores</a>
        <a href="{{ route('dashboard.barista.events.lots.create',$event->evento_id) }}" class="btn btn-primary">Novo Lote</a>
        <a href="{{ route('dashboard.barista.events.show',$event->evento_id) }}" class="btn btn-outline-secondary">Voltar ao Evento</a>
    </div>
</div>
<div class="card"><div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr><th>#</th><th>Nome</th><th>Tipo</th><th>Preço</th><th>Qtd Total</th><th>Qtd Vendida</th><th>Ativo</th><th class="text-end">Ações</th></tr>
            </thead>
            <tbody>
            @foreach($lots as $l)
                <tr>
                    <td>{{ $l->lote_id }}</td>
                    <td>{{ $l->nome }}</td>
                    <td>{{ ucfirst($l->tipo) }}</td>
                    <td>{{ $l->preco !== null ? 'R$ '.number_format($l->preco,2,',','.') : '-' }}</td>
                    <td>{{ $l->quantidade_total }}</td>
                    <td>{{ $l->quantidade_vendida }}</td>
                    <td><span class="badge {{ $l->ativo ? 'bg-success':'bg-secondary' }}">{{ $l->ativo ? 'Sim':'Não' }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('dashboard.barista.events.lots.edit',[$event->evento_id,$l->lote_id]) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                        <form action="{{ route('dashboard.barista.events.lots.destroy',[$event->evento_id,$l->lote_id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Inativar lote?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Inativar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{ $lots->links() }}
</div></div>
@endsection
