@extends('_layout.dashboard.master.layout_master')

@section('title', 'Dashboard Master')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Painel Administrativo (Master)</h1>
</div>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Usuários Totais</h5>
                <p class="display-4">0</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Eventos Hoje</h5>
                <p class="display-4">0</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h5 class="card-title">Pendentes</h5>
                <p class="display-4">0</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h5 class="card-title">Erros</h5>
                <p class="display-4">0</p>
            </div>
        </div>
    </div>
</div>
@endsection
