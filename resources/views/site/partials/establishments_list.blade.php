@if($establishments->isEmpty())
    <p class="text-muted">Nenhum estabelecimento encontrado.</p>
@else
    <div class="row g-3">
        @foreach($establishments as $establishment)
            @php
                $hasImage = !empty($establishment->imagem);
                $img = $hasImage ? asset(ltrim($establishment->imagem, '/')) : asset('uploads/logo/Logo.png');
            @endphp
            <div class="col-12 col-md-6 d-flex">
                <a href="{{ route('site.establishment.show', [$establishment->bares_id, \Illuminate\Support\Str::slug($establishment->nome)]) }}" class="text-decoration-none text-reset w-100 h-100">
                    <div class="card event-card h-100 border-0 shadow-sm w-100">
                        <div class="row g-0">
                            <div class="col-4">
                                <div class="ratio ratio-1x1">
                                    <img src="{{ $img }}" class="img-fluid rounded-start {{ $hasImage ? '' : 'placeholder-logo' }}" alt="{{ $establishment->nome }}" style="object-fit:cover;">
                                </div>
                            </div>
                            <div class="col-8">
                                <div class="card-body">
                                    <h5 class="card-title mb-1 text-truncate">{{ $establishment->nome }}</h5>
                                    <p class="small text-muted mb-1">
                                        {{ $establishment->endereco }}
                                    </p>
                                    <p class="small text-muted mb-2">
                                        {{ $establishment->bairro_nome }}
                                        @if($establishment->cidade_nome)
                                            • {{ $establishment->cidade_nome }}
                                        @endif
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small text-primary">Ver detalhes</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
    <div class="mt-3">
        {{ $establishments->withQueryString()->links() }}
    </div>
@endif
