@extends('_layout.dashboard.barista.layout_barista')
@section('title','Bases de Produtos')
@section('content')
<div class="d-flex align-items-center justify-content-between pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3 m-0">Bases de Produtos</h1>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary d-inline d-md-none" data-bs-toggle="modal" data-bs-target="#filterModalBases">
            <i class="fas fa-filter me-1"></i> Filtros
        </button>
        <a href="{{ route('dashboard.barista.products.bases.create') }}" class="btn btn-primary">Nova Base</a>
    </div>
</div>
<div class="card mb-3 d-none d-md-block">
    <div class="card-body">
        <form class="row g-2 mb-0" method="GET">
            <div class="col-md-6"><input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Pesquisar por nome"></div>
            <div class="col-md-2"><button class="btn btn-outline-primary w-100">Pesquisar</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr><th>#</th><th>Nome</th><th>Tipo</th><th>Unidade</th><th>Ativo</th><th class="text-end">Ações</th></tr>
                </thead>
                <tbody>
                @foreach($rows as $r)
                    <tr>
                        <td>{{ $r->base_id }}</td>
                        <td>{{ $r->nome }}</td>
                        <td>{{ $r->type->nome ?? '-' }}</td>
                        <td>{{ $r->unidade_padrao }}</td>
                        <td><span class="badge {{ $r->ativo ? 'bg-success':'bg-secondary' }}">{{ $r->ativo?'Sim':'Não' }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('dashboard.barista.products.bases.edit',$r->base_id) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                            <form action="{{ route('dashboard.barista.products.bases.destroy',$r->base_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Inativar esta base?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Inativar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $rows->appends(request()->query())->links() }}
    </div>
</div>

<div class="modal fade d-md-none" id="filterModalBases" tabindex="-1" aria-labelledby="filterModalBasesLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="filterModalBasesLabel"><i class="fas fa-filter me-2"></i>Filtros</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <form method="GET">
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Pesquisar por nome</label>
                <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Digite o nome">
            </div>
        </div>
        <div class="modal-footer">
          <a href="{{ route('dashboard.barista.products.bases.index') }}" class="btn btn-outline-secondary">Limpar</a>
          <button type="submit" class="btn btn-primary">Aplicar</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
