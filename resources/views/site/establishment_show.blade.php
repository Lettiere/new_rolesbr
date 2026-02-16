@extends('_layout.site.site_default')

@section('content')
<div class="container py-4">
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm mb-3">
                @php
                    $img = $establishment->imagem ? asset(ltrim($establishment->imagem, '/')) : 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=900&h=500&fit=crop';
                @endphp
                <img src="{{ $img }}" class="card-img-top" alt="{{ $establishment->nome }}">
                <div class="card-body">
                    <h1 class="h3 mb-2">{{ $establishment->nome }}</h1>
                    <p class="small text-muted mb-1">
                        {{ $establishment->endereco }}
                    </p>
                    <p class="small text-muted mb-0">
                        {{ $establishment->bairro_nome }}
                        @if($establishment->cidade_nome)
                            • {{ $establishment->cidade_nome }}
                        @endif
                    </p>
                </div>
            </div>

            @if(!$upcomingEvents->isEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Próximos eventos</h2>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($upcomingEvents as $event)
                            <a href="{{ route('site.event.show', [$event->evento_id, \Illuminate\Support\Str::slug($event->nome)]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">{{ $event->nome }}</div>
                                    <div class="small text-muted">
                                        @if($event->data_inicio)
                                            {{ \Carbon\Carbon::parse($event->data_inicio)->format('d/m') }}
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
        <div class="col-12 col-lg-4">
        </div>
    </div>
</div>
@endsection

