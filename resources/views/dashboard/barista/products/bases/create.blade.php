@extends('_layout.dashboard.barista.layout_barista')
@section('title','Nova Base de Produto')
@section('content')
<div class="d-flex align-items-center justify-content-between pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3 m-0">Nova Base de Produto</h1>
    <a href="{{ route('dashboard.barista.products.bases.index') }}" class="btn btn-outline-secondary">Voltar</a>
</div>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('dashboard.barista.products.bases.store') }}" class="row g-3">
        @csrf
        <div class="col-md-6">
            <label class="form-label">Tipo</label>
            <select name="tipo_id" class="form-select" required>
                <option value="">Selecione...</option>
                @foreach($types as $t)
                    <option value="{{ $t->tipo_id }}" @selected(old('tipo_id')==$t->tipo_id)>{{ $t->nome }}</option>
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
        <div class="col-md-6">
            <label class="form-label">Característica</label>
            <input type="text" name="caracteristica" value="{{ old('caracteristica') }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Unidade Padrão</label>
            <input type="text" name="unidade_padrao" value="{{ old('unidade_padrao') }}" class="form-control" placeholder="ex.: ml, g">
        </div>
        <div class="col-md-3">
            <label class="form-label">Tags</label>
            <input type="text" name="tags" value="{{ old('tags') }}" class="form-control" placeholder="separe por vírgula">
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
