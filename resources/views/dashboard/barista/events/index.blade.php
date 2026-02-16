@extends('_layout.dashboard.barista.layout_barista')
@section('title','Eventos')
@section('content')
<div class="d-flex align-items-center justify-content-between pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3 m-0">Eventos</h1>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary d-inline d-md-none" data-bs-toggle="modal" data-bs-target="#filterModal">
            <i class="fas fa-filter me-1"></i> Filtros
        </button>
        <a href="{{ route('dashboard.barista.events.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Novo Evento</a>
    </div>
    </div>
<div class="card mb-3 d-none d-md-block">
    <div class="card-body">
        <form class="row g-2" method="GET" id="filtrosEventos">
            <div class="col-md-3">
                <label class="form-label">Estabelecimento</label>
                <select name="bares_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    @foreach($establishments as $e)
                        <option value="{{ $e->bares_id }}" @selected(request('bares_id')==$e->bares_id)>{{ $e->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipo</label>
                <select name="tipo_evento_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    @foreach($types as $t)
                        <option value="{{ $t->tipo_evento_id }}" @selected(request('tipo_evento_id')==$t->tipo_evento_id)>{{ $t->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    @foreach(['rascunho','publicado','encerrado','cancelado'] as $s)
                        <option value="{{ $s }}" @selected(request('status')==$s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Visibilidade</label>
                <select name="visibilidade" class="form-select" onchange="this.form.submit()">
                    <option value="">Todas</option>
                    @foreach(['publico','privado','nao_listado'] as $v)
                        <option value="{{ $v }}" @selected(request('visibilidade')==$v)>{{ ucfirst(str_replace('_',' ',$v)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">Idade min.</label>
                <input type="number" name="idade_minima_min" value="{{ request('idade_minima_min') }}" class="form-control" min="0" placeholder="min">
            </div>
            <div class="col-md-1">
                <label class="form-label">até</label>
                <input type="number" name="idade_minima_max" value="{{ request('idade_minima_max') }}" class="form-control" min="0" placeholder="max">
            </div>
        </form>
    </div>
</div>
<!-- Modal de Filtros (Mobile) -->
<div class="modal fade d-md-none" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="filterModalLabel"><i class="fas fa-filter me-2"></i>Filtros</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <form method="GET" id="filtrosEventosMobile">
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Estabelecimento</label>
                <select name="bares_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach($establishments as $e)
                        <option value="{{ $e->bares_id }}" @selected(request('bares_id')==$e->bares_id)>{{ $e->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Tipo</label>
                <select name="tipo_evento_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach($types as $t)
                        <option value="{{ $t->tipo_evento_id }}" @selected(request('tipo_evento_id')==$t->tipo_evento_id)>{{ $t->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Todos</option>
                    @foreach(['rascunho','publicado','encerrado','cancelado'] as $s)
                        <option value="{{ $s }}" @selected(request('status')==$s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Visibilidade</label>
                <select name="visibilidade" class="form-select">
                    <option value="">Todas</option>
                    @foreach(['publico','privado','nao_listado'] as $v)
                        <option value="{{ $v }}" @selected(request('visibilidade')==$v)>{{ ucfirst(str_replace('_',' ',$v)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="row g-2">
                <div class="col-6">
                    <label class="form-label">Idade min.</label>
                    <input type="number" name="idade_minima_min" value="{{ request('idade_minima_min') }}" class="form-control" min="0" placeholder="min">
                </div>
                <div class="col-6">
                    <label class="form-label">até</label>
                    <input type="number" name="idade_minima_max" value="{{ request('idade_minima_max') }}" class="form-control" min="0" placeholder="max">
                </div>
            </div>
        </div>
        <div class="modal-footer">
          <a href="{{ route('dashboard.barista.events.index') }}" class="btn btn-outline-secondary">Limpar</a>
          <button type="submit" class="btn btn-primary">Aplicar</button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle m-0">
            <thead class="table-light">
                <tr>
                    <th>Nome</th>
                    <th>Estabelecimento</th>
                    <th>Tipo</th>
                    <th>Início</th>
                    <th>Status</th>
                    <th>Visibilidade</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($events as $ev)
                <tr>
                    <td>{{ $ev->nome }}</td>
                    <td>{{ $ev->establishment->nome ?? '-' }}</td>
                    <td>{{ $ev->type->nome ?? '-' }}</td>
                    <td>{{ $ev->data_inicio?->format('d/m/Y H:i') }}</td>
                    <td><span class="badge {{ $ev->status==='publicado'?'bg-success':($ev->status==='rascunho'?'bg-secondary':'bg-warning') }}">{{ $ev->status }}</span></td>
                    <td>{{ ucfirst(str_replace('_',' ',$ev->visibilidade)) }}</td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('dashboard.barista.events.show', $ev->evento_id) }}">Ver</a>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('dashboard.barista.events.edit', $ev->evento_id) }}">Editar</a>
                        <form action="{{ route('dashboard.barista.events.destroy', $ev->evento_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancelar este evento?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Cancelar</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted">Nenhum evento encontrado</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $events->appends(request()->query())->links() }}
    </div>
</div>
@endsection
