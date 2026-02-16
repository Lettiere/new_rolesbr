@extends('_layout.dashboard.rolezeiro.layout_rolezeiro')
@section('title','Checkout')
@section('content')
<div class="d-flex align-items-center justify-content-between pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3 m-0">Checkout</h1>
    <a href="{{ route('tickets.cart.index') }}" class="btn btn-outline-secondary">Voltar ao carrinho</a>
    </div>
<div class="alert alert-info">Fluxo de pagamento em implementação.</div>
@endsection
