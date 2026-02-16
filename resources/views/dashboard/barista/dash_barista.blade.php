@extends('_layout.dashboard.barista.layout_barista')

@section('title', 'Dashboard Barista')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard Barista</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">Compartilhar</button>
            <button type="button" class="btn btn-sm btn-outline-secondary">Exportar</button>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
            <span data-feather="calendar"></span>
            Esta semana
        </button>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Eventos Ativos</h5>
                <p class="card-text display-4">0</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Vendas Hoje</h5>
                <p class="card-text display-4">R$ 0,00</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title">Avaliações</h5>
                <p class="card-text display-4">5.0</p>
            </div>
        </div>
    </div>
</div>

<h2>Próximos Eventos</h2>
<div class="table-responsive">
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Nome</th>
                <th scope="col">Data</th>
                <th scope="col">Local</th>
                <th scope="col">Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1,001</td>
                <td>Exemplo Evento</td>
                <td>2024-03-20</td>
                <td>Bar Central</td>
                <td>Ativo</td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
