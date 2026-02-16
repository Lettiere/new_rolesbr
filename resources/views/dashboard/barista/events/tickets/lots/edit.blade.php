@extends('_layout.dashboard.barista.layout_barista')
@section('title','Editar Lote')
@section('content')
<div class="d-flex align-items-center justify-content-between pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3 m-0">Editar Lote — {{ $event->nome }}</h1>
    <a href="{{ route('dashboard.barista.events.lots.index',$event->evento_id) }}" class="btn btn-outline-secondary">Voltar</a>
</div>
<div class="card"><div class="card-body">
    <form class="row g-3" method="POST" action="{{ route('dashboard.barista.events.lots.update',[$event->evento_id,$lot->lote_id]) }}">
        @csrf @method('PUT')
        <div class="col-md-6">
            <label class="form-label">Nome</label>
            <input type="text" name="nome" class="form-control" value="{{ old('nome',$lot->nome) }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Tipo</label>
            <select name="tipo" class="form-select" required>
                @foreach(['gratis','desconto','inteira','meia','outro'] as $t)
                <option value="{{ $t }}" @selected(old('tipo',$lot->tipo)===$t)>{{ ucfirst($t) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Preço</label>
            <input type="number" step="0.01" name="preco" class="form-control" value="{{ old('preco',$lot->preco) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Quantidade Total</label>
            <input type="number" name="quantidade_total" class="form-control" value="{{ old('quantidade_total',$lot->quantidade_total) }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Início das Vendas</label>
            <input type="datetime-local" name="data_inicio_vendas" class="form-control" value="{{ old('data_inicio_vendas', optional($lot->data_inicio_vendas)->format('Y-m-d\\TH:i')) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Fim das Vendas</label>
            <input type="datetime-local" name="data_fim_vendas" class="form-control" value="{{ old('data_fim_vendas', optional($lot->data_fim_vendas)->format('Y-m-d\\TH:i')) }}">
        </div>
        <div class="col-12 form-check">
            <input class="form-check-input" type="checkbox" name="ativo" value="1" id="ativo" @checked(old('ativo',$lot->ativo))>
            <label class="form-check-label" for="ativo">Ativo</label>
        </div>
        <div class="col-12">
            <button class="btn btn-primary">Salvar</button>
        </div>
    </form>
</div></div>
@endsection
