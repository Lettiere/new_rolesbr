@extends('_layout.dashboard.barista.layout_barista')
@section('title','Cadastrar Álbum')
@section('content')
<div class="d-flex align-items-center justify-content-between pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3 m-0">Novo Álbum de Evento</h1>
    <a href="{{ route('dashboard.barista.events.albums') }}" class="btn btn-outline-secondary">Voltar</a>
    </div>
<div class="card">
    <div class="card-body">
        <form action="{{ route('dashboard.barista.events.albums.store') }}" method="POST" class="row g-3">
            @csrf
            <div class="col-md-8">
                <label class="form-label">Evento</label>
                <select name="evento_id" class="form-select" required>
                    <option value="">Selecione o evento...</option>
                    @foreach($events as $e)
                        <option value="{{ $e->evento_id }}" @selected((int)old('evento_id', (int)request('evento_id')) === (int)$e->evento_id)>
                            {{ $e->nome }} — {{ optional($e->data_inicio)->format('d/m/Y H:i') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Título do Álbum (opcional)</label>
                <input type="text" name="titulo" class="form-control" maxlength="150">
            </div>
            <div class="col-12">
                <label class="form-label">Descrição (opcional)</label>
                <textarea name="descricao" class="form-control" rows="2"></textarea>
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-primary">Cadastrar</button>
            </div>
        </form>
    </div>
</div>
@endsection
