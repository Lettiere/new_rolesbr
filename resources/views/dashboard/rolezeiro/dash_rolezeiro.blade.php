@extends('_layout.dashboard.rolezeiro.layout_rolezeiro')

@section('title', 'Dashboard Rolezeiro')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard Rolezeiro</h1>
</div>

<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Bem-vindo, {{ Auth::user()->name }}!</h5>
                <p class="card-text">Aqui você encontra seus ingressos, eventos favoritos e muito mais.</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">Meus Ingressos</div>
            <div class="card-body">
                <p class="text-muted">Nenhum ingresso encontrado.</p>
                <a href="#" class="btn btn-primary btn-sm">Comprar Ingressos</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">Eventos Próximos</div>
            <div class="card-body">
                <p class="text-muted">Nenhum evento próximo.</p>
                <a href="#" class="btn btn-outline-primary btn-sm">Explorar Eventos</a>
            </div>
        </div>
    </div>
</div>
@endsection
