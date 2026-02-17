@extends('_layout.site.site_default')
@section('content')
@section('meta_title', 'Produtos | RolesBr')
@section('meta_description', 'Lista de produtos e bebidas disponíveis nos estabelecimentos parceiros do RolesBr.')
@section('meta_og_type', 'website')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 fw-bold mb-0">Produtos</h1>
        <form action="{{ route('site.products.index') }}" method="GET" class="d-flex gap-2 align-items-center">
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Buscar por nome, bar ou descrição">
            </div>
            <button class="btn btn-sm btn-outline-secondary" type="button" title="Filtros">
                <i class="fas fa-filter"></i>
            </button>
        </form>
    </div>
    <div id="productsListWrapper">
        @include('site.partials.products_list', ['products' => $products])
    </div>
</div>
@push('scripts')
@push('styles')
<style>
@media (max-width: 767.98px){
    .products-list-pagination .pagination{justify-content:center;}
    .products-list-pagination .page-item{margin:0 .1rem;}
    .products-list-pagination .page-link{padding:.25rem .6rem;font-size:.8rem;border-radius:999px;min-width:2rem;text-align:center;}
}
</style>
@endpush
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('form[action="{{ route('site.products.index') }}"]');
    if (!form) return;
    var input = form.querySelector('input[name="q"]');
    var wrapper = document.getElementById('productsListWrapper');
    if (!input || !wrapper) return;
    var timer = null;
    input.addEventListener('input', function () {
        if (timer) clearTimeout(timer);
        timer = setTimeout(function () {
            var url = new URL(form.action, window.location.origin);
            if (input.value) {
                url.searchParams.set('q', input.value);
            } else {
                url.searchParams.delete('q');
            }
            url.searchParams.set('ajax', '1');
            fetch(url.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(function (r) {
                return r.text();
            }).then(function (html) {
                wrapper.innerHTML = html;
            }).catch(function () {});
        }, 300);
    });
});
</script>
@endpush
@endsection
