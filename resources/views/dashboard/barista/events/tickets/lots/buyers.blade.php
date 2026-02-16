@extends('_layout.dashboard.barista.layout_barista')
@section('title','Compradores de Ingressos')
@section('content')
<div class="d-flex align-items-center justify-content-between pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3 m-0">Compradores de Ingressos — {{ $event->nome }}</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('dashboard.barista.events.lots.index',$event->evento_id) }}" class="btn btn-outline-secondary">Voltar aos Lotes</a>
    </div>
</div>
<div class="card"><div class="card-body">
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
            <label class="form-label">Filtrar por lote</label>
            <select name="lote_id" class="form-select">
                <option value="">Todos os lotes</option>
                @foreach($lots as $l)
                    <option value="{{ $l->lote_id }}" @selected((string)$selectedLot === (string)$l->lote_id)>{{ $l->nome }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100">Filtrar</button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Código</th>
                    <th>Lote</th>
                    <th>Cliente</th>
                    <th>E-mail</th>
                    <th>CPF</th>
                    <th>Valor Pago</th>
                    <th>Status</th>
                    <th>Data da Compra</th>
                </tr>
            </thead>
            <tbody>
            @forelse($sales as $s)
                <tr>
                    <td>{{ $s->codigo_unico }}</td>
                    <td>{{ $s->lote_nome }}</td>
                    <td>{{ $s->user_name ?? $s->nome_comprador }}</td>
                    <td>{{ $s->email_comprador }}</td>
                    <td>{{ $s->cpf }}</td>
                    <td>
                        @if($s->valor_pago !== null)
                            {{ 'R$ '.number_format($s->valor_pago,2,',','.') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $s->status === 'pago' ? 'bg-success' : ($s->status === 'utilizado' ? 'bg-primary' : 'bg-secondary') }}">
                            {{ ucfirst($s->status ?? '') }}
                        </span>
                    </td>
                    <td>{{ $s->data_compra ? \Carbon\Carbon::parse($s->data_compra)->format('d/m/Y H:i') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-muted">Nenhuma compra registrada para este evento.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $sales->links() }}
</div></div>
@endsection

