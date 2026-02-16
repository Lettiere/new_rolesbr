@extends('_layout.site.site_default')

@section('content')
<div class="container-fluid px-0">
    <section class="mb-4">
        <form id="heroSearchForm" class="mb-0 mx-auto" style="max-width: 720px;" action="{{ url('/') }}" method="GET">
            <div class="input-group input-group-lg">
                <input type="text" class="form-control search-filter rounded-start" id="globalSearch" name="q" placeholder="Buscar rolê, bar, evento..." value="{{ request('q') }}">
                <button class="btn btn-warning rounded-end" type="submit" title="Pesquisar">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </section>

    <section class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4 fw-bold mb-0">
                @if(request()->filled('q'))
                    Eventos encontrados
                @else
                    Próximos eventos
                @endif
            </h2>
        </div>
        @if(empty($events) || $events->isEmpty())
            <p class="text-muted small mb-0">Nenhum evento encontrado no momento.</p>
        @else 
            <div class="row g-3">
                @foreach($events as $event)
                    <div class="col-12 col-md-6">
                        <a href="{{ route('site.event.show', [$event->evento_id, \Illuminate\Support\Str::slug($event->evento_nome)]) }}" class="text-decoration-none text-reset">
                            <div class="card event-card h-100 border-0 shadow-sm">
                                @php
                                    $cover = $event->imagem_capa ? asset(ltrim($event->imagem_capa, '/')) : 'https://images.unsplash.com/photo-1514933651103-005eec06c04b?w=600&h=350&fit=crop';
                                @endphp
                                <img src="{{ $cover }}" class="card-img-top" alt="{{ $event->evento_nome }}">
                                <div class="card-body">
                                    <h5 class="card-title mb-1 text-truncate">{{ $event->evento_nome }}</h5>
                                    <p class="small text-muted mb-1">
                                        @if(!empty($event->data_inicio))
                                            {{ \Carbon\Carbon::parse($event->data_inicio)->format('d/m') }}
                                            @if(!empty($event->hora_abertura_portas))
                                                • {{ \Carbon\Carbon::parse($event->hora_abertura_portas)->format('H:i') }}
                                            @endif
                                        @endif
                                    </p>
                                    <p class="small text-muted mb-2">
                                        {{ $event->bar_nome }}
                                        @if($event->bairro_nome || $event->cidade_nome)
                                            • {{ $event->bairro_nome }}
                                            @if($event->cidade_nome)
                                                • {{ $event->cidade_nome }}
                                            @endif
                                        @endif
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-primary">Ingressos</span>
                                        <span class="small text-primary">Ver detalhes</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4 fw-bold mb-0">Estabelecimentos</h2>
        </div>
        @if(empty($establishments) || $establishments->isEmpty())
            <p class="text-muted small mb-0">Nenhum estabelecimento encontrado.</p>
        @else
            <div class="row g-3">
                @foreach($establishments as $bar)
                    <div class="col-12 col-md-6">
                        <a href="{{ route('site.establishment.show', [$bar->bares_id, \Illuminate\Support\Str::slug($bar->nome)]) }}" class="text-decoration-none text-reset">
                            <div class="card event-card h-100 border-0 shadow-sm">
                                @php
                                    $img = $bar->imagem ? asset(ltrim($bar->imagem, '/')) : 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=600&h=350&fit=crop';
                                @endphp
                                <div class="row g-0">
                                    <div class="col-4">
                                        <div class="ratio ratio-1x1">
                                            <img src="{{ $img }}" class="img-fluid rounded-start" alt="{{ $bar->nome }}" style="object-fit:cover;">
                                        </div>
                                    </div>
                                    <div class="col-8">
                                        <div class="card-body">
                                            <h5 class="card-title mb-1 text-truncate">{{ $bar->nome }}</h5>
                                            <p class="small text-muted mb-1">
                                                {{ $bar->bairro_nome }}
                                                @if($bar->cidade_nome)
                                                    • {{ $bar->cidade_nome }}
                                                @endif
                                            </p>
                                            <p class="small mb-2">
                                                @if($bar->eventos_proximos > 0)
                                                    {{ $bar->eventos_proximos }} evento(s) próximos
                                                @else
                                                    Ainda sem eventos cadastrados
                                                @endif
                                            </p>
                                            @if($bar->tipo_perfil)
                                                <span class="badge bg-warning text-dark">{{ $bar->tipo_perfil }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
    <section class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4 fw-bold mb-0">
                @if(request()->filled('q'))
                    Ingressos encontrados
                @else
                    Ingressos em destaque
                @endif
            </h2>
        </div>
        @if(empty($tickets) || $tickets->isEmpty())
            <p class="text-muted small mb-0">Nenhum ingresso encontrado.</p>
        @else
            <div class="row g-3">
                @foreach($tickets as $ticket)
                    <div class="col-12 col-md-6">
                        <a href="{{ route('site.ticket.show', $ticket->lote_id) }}" class="text-decoration-none text-reset">
                            <div class="card event-card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title mb-1 text-truncate">{{ $ticket->lote_nome }}</h5>
                                    <p class="small text-muted mb-1">
                                        {{ $ticket->evento_nome }}
                                        @if($ticket->data_inicio)
                                            • {{ \Carbon\Carbon::parse($ticket->data_inicio)->format('d/m') }}
                                        @endif
                                    </p>
                                    <p class="small text-muted mb-2">
                                        {{ $ticket->bar_nome }}
                                        @if($ticket->bairro_nome || $ticket->cidade_nome)
                                            • {{ $ticket->bairro_nome }}
                                            @if($ticket->cidade_nome)
                                                • {{ $ticket->cidade_nome }}
                                            @endif
                                        @endif
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-success">
                                            R$ {{ number_format($ticket->preco, 2, ',', '.') }}
                                        </span>
                                        <span class="small text-primary">Ver ingresso</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
