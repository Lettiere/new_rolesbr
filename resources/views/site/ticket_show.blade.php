@extends('_layout.site.site_default')

@section('content')
<div class="container py-4">
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h1 class="h4 mb-2">{{ $ticket->nome }}</h1>
                    <p class="small text-muted mb-2">
                        {{ $ticket->tipo }}
                    </p>
                    <p class="lead mb-0">
                        R$ {{ number_format($ticket->preco, 2, ',', '.') }}
                    </p>
                    <div class="mt-3">
                        <a href="{{ route('site.ticket.checkout', $ticket->lote_id) }}" class="btn btn-primary">Comprar</a>
                    </div>
                </div>
            </div>

            @if($event)
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Evento</h2>
                </div>
                <div class="card-body">
                    <h3 class="h5 mb-1">{{ $event->nome }}</h3>
                    <p class="small text-muted mb-1">
                        @if($event->data_inicio)
                            {{ \Carbon\Carbon::parse($event->data_inicio)->translatedFormat('d \\d\\e F, H:i') }}
                        @endif
                    </p>
                    @if($event->establishment)
                        <p class="small text-muted mb-0">
                            {{ $event->establishment->nome }}
                        </p>
                    @endif
                    <a href="{{ route('site.event.show', [$event->evento_id, \Illuminate\Support\Str::slug($event->nome)]) }}" class="btn btn-outline-primary btn-sm mt-3">Ver detalhes do evento</a>
                </div>
            </div>
            @endif
        </div>
        <div class="col-12 col-lg-4">
        </div>
    </div>
</div>
@endsection
