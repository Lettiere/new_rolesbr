@extends('_layout.dashboard.barista.layout_barista')
@section('title','Novo Lote')
@section('content')
<div class="d-flex align-items-center justify-content-between pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3 m-0">Novo Lote — {{ $event->nome }}</h1>
    <a href="{{ route('dashboard.barista.events.lots.index',$event->evento_id) }}" class="btn btn-outline-secondary">Voltar</a>
</div>
<div class="card"><div class="card-body">
    <form class="row g-3" method="POST" action="{{ route('dashboard.barista.events.lots.store',$event->evento_id) }}">
        @csrf
        <div class="col-md-6">
            <label class="form-label">Nome</label>
            <input type="text" name="nome" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Tipo</label>
            <select name="tipo" class="form-select" required>
                @foreach(['gratis','desconto','inteira','meia','outro'] as $t)
                <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Preço</label>
            <input type="number" step="0.01" name="preco" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Quantidade Total</label>
            <input type="number" name="quantidade_total" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Início das Vendas</label>
            <input type="datetime-local" name="data_inicio_vendas" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Fim das Vendas</label>
            <input type="datetime-local" name="data_fim_vendas" class="form-control">
        </div>
        <div class="col-12 form-check">
            <input class="form-check-input" type="checkbox" name="ativo" value="1" id="ativo" checked>
            <label class="form-check-label" for="ativo">Ativo</label>
        </div>
        <div class="col-12">
            <button class="btn btn-primary">Salvar</button>
        </div>
    </form>
</div></div>
@endsection
