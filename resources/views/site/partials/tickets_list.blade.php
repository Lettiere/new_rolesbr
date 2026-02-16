@if($tickets->isEmpty())
    <p class="text-muted">Nenhum ingresso encontrado.</p>
@else
    <div class="row g-3">
        @foreach($tickets as $ticket)
            <div class="col-12 col-md-6 d-flex">
                <a href="{{ route('site.ticket.show', $ticket->lote_id) }}" class="text-decoration-none text-reset w-100 h-100">
                    <div class="card event-card h-100 border-0 shadow-sm w-100">
                        @php
                            $cover = $ticket->imagem_capa ? asset(ltrim($ticket->imagem_capa, '/')) : 'https://images.unsplash.com/photo-1514933651103-005eec06c04b?w=600&h=350&fit=crop';
                        @endphp
                        <img src="{{ $cover }}" class="card-img-top" alt="{{ $ticket->evento_nome }}">
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
    <div class="mt-3">
        {{ $tickets->withQueryString()->links() }}
    </div>
@endif

