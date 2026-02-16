@extends('_layout.site.site_default')

@section('content')
<div class="container py-4">
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm mb-3">
                @php
                    $cover = $event->imagem_capa ? asset(ltrim($event->imagem_capa, '/')) : 'https://images.unsplash.com/photo-1514933651103-005eec06c04b?w=900&h=500&fit=crop';
                @endphp
                <img src="{{ $cover }}" class="card-img-top" alt="{{ $event->nome }}">
                <div class="card-body">
                    <h1 class="h3 mb-2">{{ $event->nome }}</h1>
                    <p class="small text-muted mb-2">
                        @if($event->data_inicio)
                            {{ \Carbon\Carbon::parse($event->data_inicio)->translatedFormat('d \\d\\e F, H:i') }}
                        @endif
                    </p>
                    @if($event->descricao)
                        <p class="mb-0">{{ $event->descricao }}</p>
                    @endif
                    <div class="d-flex gap-2 flex-wrap mt-3">
                        @php
                            $lat = $event->latitude_evento ?: ($event->establishment->latitude ?? null);
                            $lon = $event->longitude_evento ?: ($event->establishment->longitude ?? null);
                            $addressParts = [];
                            if (!empty($event->establishment?->endereco)) $addressParts[] = $event->establishment->endereco;
                            if (!empty($event->establishment?->bairro_nome)) $addressParts[] = $event->establishment->bairro_nome;
                            if (!empty($event->establishment?->cidade_nome)) $addressParts[] = $event->establishment->cidade_nome;
                            $address = implode(', ', $addressParts);
                            $mapsQuery = ($lat && $lon) ? ($lat . ',' . $lon) : $address;
                            $mapsLink = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($mapsQuery);
                            $wazeLink = ($lat && $lon)
                                ? ('https://waze.com/ul?ll=' . $lat . ',' . $lon . '&navigate=yes')
                                : ('https://waze.com/ul?q=' . urlencode($address) . '&navigate=yes');
                            $shareTitle = trim($event->nome . ' - ' . ($event->establishment->nome ?? ''));
                            $shareText = $shareTitle . ' - ' . ($address ?: '');
                            $currentUrl = url()->current();
                            $whatsUrl = 'https://wa.me/?text=' . urlencode($shareTitle . ' ' . $currentUrl);
                        @endphp
                        <a class="btn btn-outline-primary" href="{{ $mapsLink }}" target="_blank" rel="noopener">Abrir no Maps</a>
                        <a class="btn btn-outline-primary" href="{{ $wazeLink }}" target="_blank" rel="noopener">Abrir no Waze</a>
                        <a class="btn btn-success" href="{{ $whatsUrl }}" target="_blank" rel="noopener"><i class="fab fa-whatsapp me-1"></i>WhatsApp</a>
                        <button type="button" class="btn btn-outline-secondary" onclick="shareEvent('{{ e($shareTitle) }}','{{ e($shareText) }}','{{ e($currentUrl) }}')">
                            Compartilhar
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="shareEvent('{{ e($shareTitle) }}','{{ e($shareText) }}','{{ e($currentUrl) }}')">
                            Instagram
                        </button>
                    </div>
                </div>
            </div>

            @if(!$lots->isEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0">Ingressos disponíveis</h2>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($lots as $lot)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="me-2">
                                    <div class="fw-semibold">{{ $lot->nome }}</div>
                                    <div class="small text-muted">{{ $lot->tipo }}</div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold mb-2">R$ {{ number_format($lot->preco, 2, ',', '.') }}</div>
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="{{ route('site.ticket.show', $lot->lote_id) }}" class="btn btn-outline-primary btn-sm">Ver ingresso</a>
                                        <a href="{{ route('site.ticket.checkout', $lot->lote_id) }}" class="btn btn-primary btn-sm">Comprar</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
        <div class="col-12 col-lg-4">
            @if($event->establishment)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <h2 class="h5 mb-2">Local</h2>
                        <p class="mb-1 fw-semibold">{{ $event->establishment->nome }}</p>
                        <p class="small text-muted mb-1">
                            {{ $event->establishment->endereco }}
                        </p>
                        <p class="small text-muted mb-0">
                            {{ $event->establishment->bairro_nome }}
                            @if($event->establishment->cidade_nome)
                                • {{ $event->establishment->cidade_nome }}
                            @endif
                        </p>
                        <a href="{{ route('site.establishment.show', [$event->establishment->bares_id, \Illuminate\Support\Str::slug($event->establishment->nome)]) }}" class="btn btn-outline-primary btn-sm mt-3">Ver estabelecimento</a>
                    </div>
                </div>
                @php
                    $lat = $event->latitude_evento ?: ($event->establishment->latitude ?? null);
                    $lon = $event->longitude_evento ?: ($event->establishment->longitude ?? null);
                    $addressParts = [];
                    if (!empty($event->establishment?->endereco)) $addressParts[] = $event->establishment->endereco;
                    if (!empty($event->establishment?->bairro_nome)) $addressParts[] = $event->establishment->bairro_nome;
                    if (!empty($event->establishment?->cidade_nome)) $addressParts[] = $event->establishment->cidade_nome;
                    $address = implode(', ', $addressParts);
                    $mapsQuery = ($lat && $lon) ? ($lat . ',' . $lon) : $address;
                    $embedSrc = 'https://www.google.com/maps?q=' . urlencode($mapsQuery) . '&output=embed';
                @endphp
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h2 class="h6 mb-0">Mapa do local</h2>
                    </div>
                    <div class="card-body p-0">
                        <div class="ratio ratio-4x3">
                            <iframe
                                src="{{ $embedSrc }}"
                                style="border:0;"
                                allowfullscreen
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
function shareEvent(title, text, url){
  if (navigator.share) {
    navigator.share({title: title, text: text, url: url}).catch(function(){});
  } else {
    const w = window.open('https://wa.me/?text=' + encodeURIComponent(title + ' ' + url), '_blank');
    if (!w) alert('Copie o link para compartilhar: ' + url);
  }
}
</script>
@endpush
