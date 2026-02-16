@extends('_layout.dashboard.barista.layout_barista')
@section('title','Famílias de Produtos')
@section('content')
<div class="d-flex align-items-center justify-content-between pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3 m-0">Famílias de Produtos</h1>
    <a href="{{ url('dashboard/barista/produtos') }}" class="btn btn-outline-secondary">Voltar</a>
    </div>
<div class="card">
    <div class="card-body">
        <form class="row g-2 mb-3">
            <div class="col-md-6">
                <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Pesquisar por nome">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">Pesquisar</button>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nome</th>
                        <th>Ativo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $r)
                    <tr>
                        <td>{{ $r->familia_id }}</td>
                        <td>{{ $r->nome }}</td>
                        <td><span class="badge {{ $r->ativo ? 'bg-success' : 'bg-secondary' }}">{{ $r->ativo ? 'Sim' : 'Não' }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $rows->appends(request()->query())->links() }}
    </div>
</div>
@endsection
