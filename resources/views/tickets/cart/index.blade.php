@extends('_layout.dashboard.rolezeiro.layout_rolezeiro')
@section('title','Carrinho de Ingressos')
@section('content')
<div class="d-flex align-items-center justify-content-between pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3 m-0">Carrinho de Ingressos</h1>
    <a href="{{ url('/') }}" class="btn btn-outline-secondary">Continuar navegando</a>
    </div>
<div class="card"><div class="card-body">
    @if(empty($items))
        <div class="text-muted">Seu carrinho está vazio.</div>
    @else
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light"><tr><th>Lote</th><th>Evento</th><th>Preço</th><th>Qtd</th><th class="text-end">Subtotal</th><th></th></tr></thead>
            <tbody>
            @foreach($items as $it)
                <tr>
                    <td>{{ $it['lot']->nome }}</td>
                    <td>{{ $it['lot']->evento_id }}</td>
                    <td>{{ $it['lot']->preco !== null ? 'R$ '.number_format($it['lot']->preco,2,',','.') : 'Grátis' }}</td>
                    <td>{{ $it['qty'] }}</td>
                    <td class="text-end">{{ 'R$ '.number_format($it['subtotal'],2,',','.') }}</td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('tickets.cart.remove') }}">
                            @csrf
                            <input type="hidden" name="lote_id" value="{{ $it['lot']->lote_id }}">
                            <button class="btn btn-sm btn-outline-danger">Remover</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-end">
        <div class="fs-5 fw-bold">Total: {{ 'R$ '.number_format($total,2,',','.') }}</div>
    </div>
    <div class="mt-3 text-end">
        <a href="{{ route('tickets.cart.checkout') }}" class="btn btn-primary">Ir para pagamento</a>
    </div>
    @endif
</div></div>
@endsection
