@extends('_layout.dashboard.barista.layout_barista')
@section('title','Novo Tipo de Produto')
@section('content')
<div class="d-flex align-items-center justify-content-between pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3 m-0">Novo Tipo de Produto</h1>
    <a href="{{ route('dashboard.barista.products.types.index') }}" class="btn btn-outline-secondary">Voltar</a>
</div>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('dashboard.barista.products.types.store') }}" class="row g-3">
        @csrf
        <div class="col-md-6">
            <label class="form-label">Família</label>
            <select name="familia_id" class="form-select" required>
                <option value="">Selecione...</option>
                @foreach($families as $f)
                    <option value="{{ $f->familia_id }}" @selected(old('familia_id')==$f->familia_id)>{{ $f->nome }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Nome</label>
            <input type="text" name="nome" value="{{ old('nome') }}" class="form-control" required>
        </div>
        <div class="col-12">
            <label class="form-label">Descrição</label>
            <textarea name="descricao" class="form-control" rows="3">{{ old('descricao') }}</textarea>
        </div>
        <div class="col-12 form-check">
            <input type="checkbox" name="ativo" value="1" id="ativo" class="form-check-input" checked>
            <label class="form-check-label" for="ativo">Ativo</label>
        </div>
        <div class="col-12">
            <button class="btn btn-primary">Salvar</button>
        </div>
    </form>
</div></div>
@endsection
