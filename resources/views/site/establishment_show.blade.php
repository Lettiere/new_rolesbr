@extends('_layout.site.site_default')

@section('content')
@php
    $img = $establishment->imagem ? asset(ltrim($establishment->imagem, '/')) : null;
    $cover = $img ?: 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=900&h=500&fit=crop';

    $hIni = trim((string)($establishment->horario_inicio ?? ''));
    if (preg_match('/^\d{4}$/', $hIni)) {
        $hIni = substr($hIni, 0, 2).':'.substr($hIni, 2, 2);
    }
    $hFim = trim((string)($establishment->horario_final ?? ''));
    if (preg_match('/^\d{4}$/', $hFim)) {
        $hFim = substr($hFim, 0, 2).':'.substr($hFim, 2, 2);
    }
    $hoursLabel = $hIni && $hFim ? $hIni.' - '.$hFim : ($hIni ?: ($hFim ?: null));
    $capacity = $establishment->capacidade ?? null;

    // métricas (adapte para sua lógica real)
    $likesCount = $establishment->likes_count ?? 0;
    $followersCount = $establishment->followers_count ?? 0;

    $slug = \Illuminate\Support\Str::slug($establishment->nome ?: 'estabelecimento');
    $shareUrl = url('estabelecimento/'.$establishment->bares_id.'-'.$slug);
@endphp

<div class="establishment-page w-100" style="margin-top:-18px;">
    {{-- HEADER TIPO PERFIL --}}
    <div class="position-relative w-100">
        <div class="w-100" style="height:220px; background:linear-gradient(90deg,#020617,#b8860b);"></div>
        <img src="{{ $cover }}" alt="{{ $establishment->nome }}"
             class="w-100"
             style="height:220px; object-fit:cover; position:absolute; inset:0; opacity:.35;">
        <div class="container-fluid position-relative px-3 px-md-4" style="top:-40px;">
            <div class="d-flex flex-wrap gap-3 align-items-end">
                {{-- avatar / foto de perfil --}}
                <div class="rounded-circle border border-3 border-light overflow-hidden"
                     style="width:96px;height:96px;background:#fff;">
                    <img src="{{ $cover }}" alt="{{ $establishment->nome }}"
                         class="w-100 h-100" style="object-fit:cover;">
                </div>

                {{-- info principal --}}
                <div class="flex-grow-1">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <h1 class="h4 fw-bold m-0 text-dark">{{ $establishment->nome }}</h1>
                        @if(!empty($tipo) && !empty($tipo->nome))
                            <span class="badge bg-light text-dark">{{ $tipo->nome }}</span>
                        @endif
                    </div>
                    <div class="small text-muted">
                        {{ $establishment->bairro_nome }}
                        @if($establishment->cidade_nome)
                            • {{ $establishment->cidade_nome }}
                        @endif
                    </div>

                    {{-- badges de status (horário, capacidade, nome na lista) --}}
                    @if($hoursLabel || $capacity || $establishment->nome_na_lista)
                        <div class="small mt-2 d-flex flex-wrap gap-2">
                            @if($hoursLabel)
                                <span class="badge bg-light text-dark">
                                    <i class="far fa-clock me-1"></i>{{ $hoursLabel }}
                                </span>
                            @endif
                            @if($capacity)
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-user-friends me-1"></i>Até {{ $capacity }} pessoas
                                </span>
                            @endif
                            @if($establishment->nome_na_lista)
                                <span class="badge bg-success text-white">
                                    <i class="fas fa-list-ul me-1"></i>Nome na lista
                                </span>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- ações estilo Insta --}}
                <div class="ms-md-auto d-flex flex-column align-items-end gap-2">
                    {{-- métricas --}}
                    <div class="d-flex align-items-center gap-3 small text-muted">
                        <div>
                            <span class="fw-bold">{{ $likesCount }}</span>
                            <span>curtidas</span>
                        </div>
                        <div>
                            <span class="fw-bold">{{ $followersCount }}</span>
                            <span>seguidores</span>
                        </div>
                    </div>
                    {{-- botões --}}
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button"
                                class="btn btn-sm btn-outline-light text-dark d-flex align-items-center gap-1"
                                data-role="like-bar">
                            <i class="far fa-heart"></i>
                            <span>Curtir</span>
                        </button>
                        <button type="button"
                                class="btn btn-sm btn-primary d-flex align-items-center gap-1"
                                data-role="follow-bar">
                            <i class="far fa-bookmark"></i>
                            <span>Seguir</span>
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown">
                                Compartilhar
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item"
                                       href="https://wa.me/?text={{ urlencode($establishment->nome.' - '.$shareUrl) }}"
                                       target="_blank" rel="noopener">
                                        WhatsApp
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item"
                                       href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}"
                                       target="_blank" rel="noopener">
                                        Facebook
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item"
                                       href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ urlencode($establishment->nome) }}"
                                       target="_blank" rel="noopener">
                                        Twitter/X
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- CONTEÚDO "SOLTO" (SEM ABAS) --}}
    <div class="container">
        <div class="row g-4 py-3">
            <div class="col-12">

                {{-- endereço + ações rápidas + mapa --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        @if($establishment->endereco)
                            <p class="small text-muted mb-1">
                                {{ $establishment->endereco }}
                            </p>
                        @endif
                        <p class="small text-muted mb-3">
                            {{ $establishment->bairro_nome }}
                            @if($establishment->cidade_nome)
                                • {{ $establishment->cidade_nome }}
                            @endif
                        </p>

                        @php
                            $addr = trim(($establishment->endereco ?? '').' '.($establishment->bairro_nome ?? '').' '.($establishment->cidade_nome ?? ''));
                            $mapsLink = $addr ? 'https://www.google.com/maps/search/?api=1&query='.urlencode($addr) : null;
                            $wazeLink = ($establishment->latitude && $establishment->longitude)
                                ? ('https://waze.com/ul?ll='.urlencode($establishment->latitude.','.$establishment->longitude).'&navigate=yes')
                                : ($addr ? ('https://waze.com/ul?query='.urlencode($addr).'&navigate=yes') : null);
                            $waNumber = preg_replace('/\D+/', '', (string)($establishment->telefone ?? ''));
                            $waLink = $waNumber ? ('https://wa.me/55'.$waNumber.'?text='.urlencode('Olá, encontrei seu perfil no RolesBr: '.($establishment->nome ?? ''))) : null;
                            $lat = $establishment->latitude;
                            $lon = $establishment->longitude;
                            $mapsQuery = ($lat && $lon) ? ($lat.','.$lon) : $addr;
                            $mapEmbedSrc = $mapsQuery ? ('https://www.google.com/maps?q='.urlencode($mapsQuery).'&output=embed') : null;
                        @endphp

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @if($waLink)
                                <a href="{{ $waLink }}" target="_blank" rel="noopener"
                                   class="btn btn-outline-success btn-sm">
                                    <i class="fab fa-whatsapp me-1"></i>WhatsApp
                                </a>
                            @endif
                            @if($establishment->site)
                                <a href="{{ $establishment->site }}" target="_blank" rel="noopener"
                                   class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-globe me-1"></i>Site
                                </a>
                            @endif
                            @if($mapsLink)
                                <a href="{{ $mapsLink }}" target="_blank" rel="noopener"
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-map-marker-alt me-1"></i>Maps
                                </a>
                            @endif
                            @if($wazeLink)
                                <a href="{{ $wazeLink }}" target="_blank" rel="noopener"
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-location-arrow me-1"></i>Waze
                                </a>
                            @endif
                        </div>
                        @if($mapEmbedSrc)
                            <div class="ratio ratio-16x9 mt-3">
                                <iframe src="{{ $mapEmbedSrc }}" style="border:0;" loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- sobre + benefícios --}}
                @php
                    $benefRaw = (string)($establishment->beneficios ?? '');
                    $benefBlocks = null;
                    $benefText = null;
                    if ($benefRaw !== '') {
                        $firstChar = substr($benefRaw, 0, 1);
                        if ($firstChar === '{') {
                            $tmp = json_decode($benefRaw, true);
                            if (is_array($tmp)) {
                                $benefBlocks = $tmp;
                            } else {
                                $benefText = $benefRaw;
                            }
                        } else {
                            $benefText = $benefRaw;
                        }
                    }
                @endphp

                @if($establishment->descricao || $benefText || $benefBlocks)
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            @if($establishment->descricao)
                                <h2 class="h6 fw-bold mb-1">Sobre o estabelecimento</h2>
                                <p class="small text-muted mb-3">{{ $establishment->descricao }}</p>
                            @endif

                            @if($benefText || $benefBlocks)
                                <h2 class="h6 fw-bold mb-2">Benefícios</h2>
                            @endif

                            @if($benefText)
                                <p class="small text-muted mb-2">{!! nl2br(e($benefText)) !!}</p>
                            @endif

                            @if($benefBlocks)
                                @php
                                    $benefMap = [
                                        'comodidades' => 'Comodidades',
                                        'entretenimento' => 'Entretenimento',
                                        'ofertas' => 'Ofertas',
                                        'servicos' => 'Serviços',
                                    ];
                                @endphp
                                <div class="row small">
                                    @foreach($benefMap as $key => $label)
                                        @php
                                            $items = (isset($benefBlocks[$key]) && is_array($benefBlocks[$key])) ? $benefBlocks[$key] : [];
                                        @endphp
                                        @if(!empty($items))
                                            <div class="col-12 col- mb-2">
                                                <div class="fw-semibold mb-1">{{ $label }}</div>
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($items as $val)
                                                        <span class="badge bg-light text-dark">{{ $val }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- galeria (sem aba, já direto) --}}
                @php
                    $hasAlbums = !empty($fotos) && !$fotos->isEmpty();
                @endphp
                @if($hasAlbums)
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h2 class="h6 fw-bold mb-0">Galeria</h2>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Compartilhar</button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item"
                                               href="https://wa.me/?text={{ urlencode('Galeria - '.$shareUrl) }}"
                                               target="_blank" rel="noopener">
                                                <i class="fab fa-whatsapp me-1"></i>WhatsApp
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#"
                                               onclick="navigator.clipboard.writeText('{{ $shareUrl }}');return false;">
                                                <i class="fas fa-link me-1"></i>Copiar link
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="row g-2">
                                @foreach($fotos as $f)
                                    @php
                                        $raw = (string)($f->url ?? '');
                                        $norm = str_replace('\\','/',$raw);
                                        $u = $norm ? asset(ltrim($norm,'/')) : 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=400&h=200&fit=crop';
                                    @endphp
                                    <div class="col-12 col-md-4">
                                        <button type="button"
                                                class="btn p-0 border-0 w-100 text-start est-gallery-item"
                                                data-gallery-index="{{ $loop->index }}"
                                                data-gallery-src="{{ $u }}">
                                            <img src="{{ $u }}" alt="Foto"
                                                 class="img-fluid rounded"
                                                 style="object-fit:cover; height:140px; width:100%;">
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- próximos eventos (se existirem) --}}
                @php
                    $hasEvents = !$upcomingEvents->isEmpty();
                @endphp
                @if($hasEvents)
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h2 class="h6 fw-bold mb-0">Próximos eventos</h2>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Compartilhar</button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item"
                                               href="https://wa.me/?text={{ urlencode('Eventos - '.$shareUrl) }}"
                                               target="_blank" rel="noopener">
                                                <i class="fab fa-whatsapp me-1"></i>WhatsApp
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#"
                                               onclick="navigator.clipboard.writeText('{{ $shareUrl }}');return false;">
                                                <i class="fas fa-link me-1"></i>Copiar link
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="row g-3">
                                @foreach($upcomingEvents as $event)
                                    @php
                                        $eventCover = $event->imagem_capa ? asset(ltrim($event->imagem_capa, '/'))
                                                    : 'https://images.unsplash.com/photo-1514933651103-005eec06c04b?w=600&h=350&fit=crop';
                                    @endphp
                                    <div class="col-12 col-md-6">
                                        <a href="{{ route('site.event.show', [$event->evento_id, \Illuminate\Support\Str::slug($event->nome)]) }}"
                                           class="text-decoration-none text-reset">
                                            <div class="card h-100 border-0 shadow-sm">
                                                <img src="{{ $eventCover }}"
                                                     class="card-img-top"
                                                     alt="{{ $event->nome }}"
                                                     style="object-fit:cover;height:160px;">
                                                <div class="card-body p-3">
                                                    <h6 class="fw-bold mb-1 text-truncate">{{ $event->nome }}</h6>
                                                    @if($event->data_inicio)
                                                        <p class="small text-muted mb-0">
                                                            {{ \Carbon\Carbon::parse($event->data_inicio)->format('d/m/Y H:i') }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- cardápios (lista única, sem aba) --}}
                @php
                    $hasMenus = !empty($cardapios) && !$cardapios->isEmpty();
                @endphp
                @if($hasMenus)
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <h2 class="h6 fw-bold mb-2">Cardápio</h2>
                            <div class="accordion" id="menuAccordion">
                                @foreach($cardapios as $c)
                                    @php
                                        $accId = 'cardapio-'.$c->cardapio_id;
                                    @endphp
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading-{{ $accId }}">
                                            <button class="accordion-button collapsed" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#collapse-{{ $accId }}"
                                                    aria-expanded="false"
                                                    aria-controls="collapse-{{ $accId }}">
                                                <div class="d-flex flex-column flex-md-row w-100 justify-content-between align-items-md-center">
                                                    <div>
                                                        <span class="fw-bold d-block">{{ $c->nome }}</span>
                                                        @if($c->descricao)
                                                            <small class="text-muted d-block">{{ $c->descricao }}</small>
                                                        @endif
                                                    </div>
                                                    <span class="badge bg-light text-dark mt-2 mt-md-0">
                                                        {{ strtoupper($c->tipo_cardapio) }}
                                                    </span>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="collapse-{{ $accId }}" class="accordion-collapse collapse"
                                             aria-labelledby="heading-{{ $accId }}"
                                             data-bs-parent="#menuAccordion">
                                            <div class="accordion-body">
                                                @php
                                                    $items = $cardapioItens->where('cardapio_id', $c->cardapio_id);
                                                    $grouped = $items->groupBy(function($i){ return $i->categoria ?: 'Outros'; });
                                                @endphp
                                                @forelse($grouped as $categoria => $lista)
                                                    <h6 class="fw-bold mt-3">{{ $categoria }}</h6>
                                                    <div class="row g-2">
                                                        @foreach($lista as $i)
                                                            @php
                                                                $preco = $i->preco_override ?? $i->produto_preco;
                                                                $prodSlug = \Illuminate\Support\Str::slug($i->produto_nome ?? 'produto');
                                                            @endphp
                                                            <div class="col-sm-6">
                                                                <a href="{{ route('site.product.show', [$i->prod_id, $prodSlug]) }}"
                                                                   class="text-decoration-none text-reset">
                                                                    <div class="d-flex justify-content-between align-items-center border rounded p-2">
                                                                        <div>
                                                                            <div class="fw-semibold">{{ $i->produto_nome }}</div>
                                                                            @if($i->unidade)
                                                                                <small class="text-muted d-block">{{ $i->unidade }}</small>
                                                                            @endif
                                                                            @if($i->observacoes)
                                                                                <div class="small text-muted">{{ $i->observacoes }}</div>
                                                                            @endif
                                                                        </div>
                                                                        @if($preco)
                                                                            <div class="fw-bold text-primary">
                                                                                R$ {{ number_format((float)$preco, 2, ',', '.') }}
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </a>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @empty
                                                    <small class="text-muted">Sem itens cadastrados</small>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- campo de busca interno (se quiser manter) --}}
                <div class="mb-3">
                    <input type="text" class="form-control form-control-sm"
                           placeholder="Pesquisar neste estabelecimento">
                </div>

            </div>

            {{-- coluna lateral direita --}}
            <div class="col-12">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                            <div>
                                <h2 class="h6 fw-bold mb-1">Avaliação do estabelecimento</h2>
                                <p class="small text-muted mb-0">
                                    Em breve você poderá avaliar este local e ver a nota média.
                                </p>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="text-warning" style="font-size:1.3rem;">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                    <i class="far fa-star"></i>
                                </div>
                                <div class="small text-muted">
                                    <span class="fw-bold">4,5</span>/5
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h2 class="h6 fw-bold mb-2">Comentários</h2>
                        <p class="small text-muted mb-0">
                            Espaço reservado para comentários e avaliações dos usuários.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- lightbox da galeria (mesmo JS que você já tinha) --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var items = Array.prototype.slice.call(document.querySelectorAll('.est-gallery-item'));
    if (!items.length) return;

    var currentIndex = 0;
    var overlay = null, img = null, btnClose = null, btnPrev = null, btnNext = null;

    function ensureOverlay() {
        if (overlay) return;
        overlay = document.createElement('div');
        overlay.className = 'est-gallery-lightbox d-flex';
        overlay.style.position = 'fixed';
        overlay.style.inset = '0';
        overlay.style.zIndex = '1055';
        overlay.style.background = 'rgba(0,0,0,.88)';
        overlay.style.display = 'none';
        overlay.style.alignItems = 'center';
        overlay.style.justifyContent = 'center';
        overlay.innerHTML = ''
            + '<button type="button" class="btn btn-light position-absolute" data-role="close" style="top:12px;right:16px;border-radius:50%;width:36px;height:36px;padding:0;font-size:1.4rem;line-height:1;">&times;</button>'
            + '<button type="button" class="btn btn-light position-absolute" data-role="prev" style="left:10px;">&#8249;</button>'
            + '<img data-role="image" src="" alt="Foto" style="max-width:90vw;max-height:90vh;box-shadow:0 0 25px rgba(0,0,0,.6);border-radius:8px;object-fit:contain;cursor:zoom-in;">'
            + '<button type="button" class="btn btn-light position-absolute" data-role="next" style="right:10px;">&#8250;</button>';
        document.body.appendChild(overlay);
        img = overlay.querySelector('img[data-role="image"]');
        btnClose = overlay.querySelector('[data-role="close"]');
        btnPrev = overlay.querySelector('[data-role="prev"]');
        btnNext = overlay.querySelector('[data-role="next"]');

        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) hide();
        });
        btnClose.addEventListener('click', function (e) {
            e.preventDefault(); hide();
        });
        btnNext.addEventListener('click', function (e) {
            e.preventDefault(); next();
        });
        btnPrev.addEventListener('click', function (e) {
            e.preventDefault(); prev();
        });
        img.addEventListener('click', function () { next(); });
        document.addEventListener('keydown', function (e) {
            if (!overlay || overlay.style.display !== 'flex') return;
            if (e.key === 'Escape') hide();
            else if (e.key === 'ArrowRight') next();
            else if (e.key === 'ArrowLeft') prev();
        });
    }

    function show(index) {
        if (!items.length) return;
        ensureOverlay();
        if (index < 0) index = items.length - 1;
        if (index >= items.length) index = 0;
        currentIndex = index;
        var src = items[currentIndex].getAttribute('data-gallery-src');
        if (!src || !img || !overlay) return;
        img.src = src;
        img.style.cursor = 'zoom-in';
        overlay.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    function hide() {
        if (!overlay) return;
        overlay.style.display = 'none';
        document.body.style.overflow = '';
    }
    function next() { show(currentIndex + 1); }
    function prev() { show(currentIndex - 1); }

    items.forEach(function (el, idx) {
        el.addEventListener('click', function (e) {
            e.preventDefault(); show(idx);
        });
    });
});
</script>
@endpush
@endsection
