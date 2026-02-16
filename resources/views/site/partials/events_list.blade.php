@if($events->isEmpty())
    <p class="text-muted">Nenhum evento encontrado.</p>
@else
    <div class="row g-3">
        @foreach($events as $event)
            <div class="col-12 col-md-6 d-flex">
                <a href="{{ route('site.event.show', [$event->evento_id, \Illuminate\Support\Str::slug($event->evento_nome)]) }}" class="text-decoration-none text-reset w-100 h-100">
                    <div class="card event-card h-100 border-0 shadow-sm w-100">
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
    <div class="mt-3">
        {{ $events->withQueryString()->links() }}
    </div>
@endif

