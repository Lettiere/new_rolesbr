@if($products->isEmpty())
    <p class="text-muted">Nenhum produto encontrado.</p>
@else
    <div class="row g-3">
        @foreach($products as $prod)
            <div class="col-12 col-md-6 d-flex">
                <a href="{{ route('site.product.show', [$prod->prod_id, \Illuminate\Support\Str::slug($prod->nome)]) }}" class="text-decoration-none text-reset w-100 h-100">
                    <div class="card event-card h-100 border-0 shadow-sm w-100">
                        <div class="card-body">
                            <h5 class="card-title mb-1 text-truncate">{{ $prod->nome }}</h5>
                            <p class="small text-muted mb-1">{{ $prod->bar_nome }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">
                                    R$ {{ number_format($prod->preco ?? 0, 2, ',', '.') }}
                                </span>
                                <span class="small text-primary">Ver produto</span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
    <div class="mt-3">
        {{ $products->withQueryString()->links() }}
    </div>
@endif

