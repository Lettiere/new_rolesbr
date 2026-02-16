@extends('_layout.dashboard.barista.layout_barista')
@section('title','Novo Tipo de Evento')
@section('content')
<div class="d-flex align-items-center justify-content-between pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3 m-0">Novo Tipo de Evento</h1>
    <a href="{{ route('dashboard.barista.events.types') }}" class="btn btn-outline-secondary">Voltar</a>
    </div>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('dashboard.barista.events.types.store') }}" class="row g-3">
        @csrf
        <div class="col-md-6">
            <label class="form-label">Nome</label>
            <input type="text" name="nome" value="{{ old('nome') }}" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Categoria</label>
            <input type="text" name="categoria" value="{{ old('categoria') }}" class="form-control">
        </div>
        <div class="col-12">
            <label class="form-label">Descrição</label>
            <textarea name="descricao" class="form-control" rows="3">{{ old('descricao') }}</textarea>
        </div>
        <div class="col-12 form-check">
            <input class="form-check-input" type="checkbox" name="ativo" value="1" id="ativo" @checked(old('ativo',true))>
            <label class="form-check-label" for="ativo">Ativo</label>
        </div>
        <div class="col-12">
            <button class="btn btn-primary">Salvar</button>
        </div>
    </form>
</div></div>
@endsection
