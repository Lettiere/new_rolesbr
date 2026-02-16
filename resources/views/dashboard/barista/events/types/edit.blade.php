@extends('_layout.dashboard.barista.layout_barista')
@section('title','Editar Tipo de Evento')
@section('content')
<div class="d-flex align-items-center justify-content-between pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3 m-0">Editar Tipo de Evento</h1>
    <a href="{{ route('dashboard.barista.events.types') }}" class="btn btn-outline-secondary">Voltar</a>
    </div>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('dashboard.barista.events.types.update',$row->tipo_evento_id) }}" class="row g-3">
        @csrf @method('PUT')
        <div class="col-md-6">
            <label class="form-label">Nome</label>
            <input type="text" name="nome" value="{{ old('nome',$row->nome) }}" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Categoria</label>
            <input type="text" name="categoria" value="{{ old('categoria',$row->categoria) }}" class="form-control">
        </div>
        <div class="col-12">
            <label class="form-label">Descrição</label>
            <textarea name="descricao" class="form-control" rows="3">{{ old('descricao',$row->descricao) }}</textarea>
        </div>
        <div class="col-12 form-check">
            <input class="form-check-input" type="checkbox" name="ativo" value="1" id="ativo" @checked(old('ativo',$row->ativo))>
            <label class="form-check-label" for="ativo">Ativo</label>
        </div>
        <div class="col-12">
            <button class="btn btn-primary">Salvar</button>
        </div>
    </form>
</div></div>
@endsection
