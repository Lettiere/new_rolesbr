@extends('_layout.site.site_default')

@section('content')
<div class="container-fluid px-0">
@php
    $featured = $featuredEvents ?? collect();
@endphp
<!-- search -->
 <section class="mb-4">
        <form id="heroSearchForm" class="mb-0 mx-auto" style="max-width: 720px;" action="{{ url('/') }}" method="GET">
            <div class="input-group input-group-lg">
                <input type="text" class="form-control search-filter rounded-start" id="globalSearch" name="q" placeholder="Buscar rolê, bar, evento..." value="{{ request('q') }}">
                <button class="btn btn-warning" type="submit" title="Pesquisar">
                    <i class="fas fa-search"></i>
                </button>
                <button class="btn btn-outline-secondary rounded-end" type="button" title="Filtros" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="fas fa-filter"></i>
                </button>
            </div>
        </form>
    </section>
    
<section class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2 d-none">
        <h2 class="h5 fw-bold mb-0">Stories dos Roles</h2>
        <a id="storiesLink" href="/stories" class="inline-flex items-center px-3 py-1 rounded-full bg-gradient-to-r from-orange-500 to-pink-500 text-white text-xs shadow hover:opacity-90">
            Ver mais
        </a>
    </div>

    <div class="position-relative">
        <button class="btn btn-sm btn-light position-absolute top-50 start-0 translate-middle-y shadow d-none d-md-inline-flex"
                type="button" id="storiesPrev">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="btn btn-sm btn-light position-absolute top-50 end-0 translate-middle-y shadow d-none d-md-inline-flex"
                type="button" id="storiesNext">
            <i class="fas fa-chevron-right"></i>
        </button>

        <div id="storiesScroll"
             class="stories-wrapper d-flex flex-row overflow-auto py-2 px-1"
             style="scroll-behavior:smooth; gap:12px;">
            <div id="storiesItems" class="d-flex" style="gap:12px;"></div>
        </div>
</div>

 
<script>
let allStories = [];
let currentStoryIndex = 0;
try {
    const link = window.STORIES_LINK || '/stories';
    const a = document.getElementById('storiesLink');
    if (a) a.href = link;
} catch(e) {}

function storyItem(s, index){
    const img = s.image_url ? ('/' + encodeURI(s.image_url)) : 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=200';
    const name = s.name || s.user_name || 'Story';
    return `<button class="border-0 bg-transparent p-0 text-center" onclick="openStoryModal(${index})" type="button">
        <div class="rounded-circle p-1 shadow" style="background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);">
            <div class="rounded-circle border border-3 border-white" style="width:84px;height:84px;overflow:hidden;">
                <img src="${img}" alt="${name}" class="w-100 h-100" style="object-fit:cover;">
            </div>
        </div>
        <small class="d-block mt-2 text-truncate" style="max-width:96px;">${name}</small>
    </button>`;
}

function openStoryModal(index) {
    if (!allStories || allStories.length === 0) return;
    currentStoryIndex = index;
    const story = allStories[index];
    const imgUrl = story.image_url ? ('/' + encodeURI(story.image_url)) : '';
    const img = document.getElementById('storyModalImage');
    const caption = document.getElementById('storyCaption');
    if (img && imgUrl) {
        img.src = imgUrl;
        if (caption) {
            caption.textContent = story.name || story.user_name || 'Story';
        }
        const modal = new bootstrap.Modal(document.getElementById('storyModal'));
        modal.show();
    }
    // preload vizinho
    const nextIdx = (index + 1) % allStories.length;
    const prevIdx = (index - 1 + allStories.length) % allStories.length;
    [nextIdx, prevIdx].forEach(i => {
        const s = allStories[i];
        if (s && s.image_url) {
            const preload = new Image();
            preload.src = '/' + encodeURI(s.image_url);
        }
    });
}

function changeStory(direction) {
    if (!allStories || allStories.length === 0) return;
    currentStoryIndex = (currentStoryIndex + direction + allStories.length) % allStories.length;
    const story = allStories[currentStoryIndex];
    const imgUrl = story.image_url ? ('/' + encodeURI(story.image_url)) : '';
    const img = document.getElementById('storyModalImage');
    const caption = document.getElementById('storyCaption');
    if (img && imgUrl) {
        img.src = imgUrl;
        if (caption) {
            caption.textContent = story.name || story.user_name || 'Story';
        }
    }
}

async function loadStories(){
    const items=document.getElementById('storiesItems');
    items.innerHTML='<div class="text-muted small">Carregando...</div>';
    try{
        const limit = window.STORIES_LIMIT || 12;
        const endpoint = window.STORIES_ENDPOINT || '/home/stories-all';
        const r=await fetch(endpoint + '?limit='+limit+'&_ts='+Date.now(),{headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
        if(!r.ok){ items.innerHTML='<div class="text-danger small">Erro ao carregar</div>'; return; }
        const data=await r.json();
        if(!Array.isArray(data)||!data.length){ items.innerHTML='<div class="text-muted small">Sem stories</div>'; return; }
        const valid = data.filter(s => !!s.image_url);
        if(!valid.length){ items.innerHTML='<div class="text-muted small">Sem stories</div>'; return; }
        allStories = valid;
        items.innerHTML=allStories.map((s, i) => storyItem(s, i)).join('');
    }catch(e){
        items.innerHTML='<div class="text-muted small">Falha de rede</div>';
        console.error(e);
    }
}
function initStoriesScroll(){
    const scrollEl=document.getElementById('storiesScroll');
    const prevBtn=document.getElementById('storiesPrev');
    const nextBtn=document.getElementById('storiesNext');
    const step=160;
    prevBtn.addEventListener('click', ()=> scrollEl.scrollBy({left:-step, behavior:'smooth'}));
    nextBtn.addEventListener('click', ()=> scrollEl.scrollBy({left:step, behavior:'smooth'}));
}
function initStoriesKeys(){
    document.addEventListener('keydown', function(e){
        const modal = document.getElementById('storyModal');
        const hasShow = modal && modal.classList.contains('show');
        if (!hasShow) return;
        if (e.key === 'ArrowRight') changeStory(1);
        if (e.key === 'ArrowLeft') changeStory(-1);
        if (e.key === 'Escape') bootstrap.Modal.getInstance(modal)?.hide();
    });
}
if(document.readyState!=='loading'){ loadStories(); initStoriesScroll(); initStoriesKeys(); } else { document.addEventListener('DOMContentLoaded', ()=>{ loadStories(); initStoriesScroll(); initStoriesKeys(); }); }
</script>

<!-- Modal para exibir story -->
<div class="modal fade" id="storyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
        <div class="modal-content bg-dark">
            <div class="modal-body p-0 position-relative">
                <button type="button" class="btn btn-light position-absolute top-50 start-0 translate-middle-y m-2" onclick="changeStory(-1)" style="z-index:10;">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button type="button" class="btn btn-light position-absolute top-50 end-0 translate-middle-y m-2" onclick="changeStory(1)" style="z-index:10;">
                    <i class="fas fa-chevron-right"></i>
                </button>
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2" data-bs-dismiss="modal" aria-label="Fechar" style="z-index:10;"></button>
                <img id="storyModalImage" src="" class="img-fluid w-100" style="max-height:100vh;object-fit:contain;" alt="Story">
                <div class="position-absolute bottom-0 start-0 end-0 p-2 text-center text-white" style="background:linear-gradient(180deg, rgba(0,0,0,0), rgba(0,0,0,.6));">
                    <span id="storyCaption" class="small"></span>
                </div>
            </div>
        </div>
    </div>
</div>




    
  <!-- destaque -->
     @if($featured && $featured->count() > 0)
    <section class="mb-4">
        <div id="eventPromoCarousel" class="carousel slide shadow-sm rounded" data-bs-ride="carousel">
            <div class="carousel-indicators">
                @foreach($featured as $index => $fe)
                    <button type="button"
                            data-bs-target="#eventPromoCarousel"
                            data-bs-slide-to="{{ $index }}"
                            @if($index === 0) class="active" aria-current="true" @endif
                            aria-label="Slide {{ $index + 1 }}"></button>
                @endforeach
            </div>
            <div class="carousel-inner">
                @foreach($featured as $index => $fe)
                    @php
                        $nome = $fe->nome ?? $fe->evento_nome ?? 'Evento';
                        $cover = $fe->imagem_capa ? asset(ltrim($fe->imagem_capa, '/')) : 'https://images.unsplash.com/photo-1514933651103-005eec06c04b?w=1200&h=675&fit=crop';
                        $when = !empty($fe->data_inicio) ? \Carbon\Carbon::parse($fe->data_inicio)->format('d/m') : '';
                        $where = $fe->cidade_nome ?? '';
                        $slug = \Illuminate\Support\Str::slug($nome);
                    @endphp
                    <div class="carousel-item @if($index === 0) active @endif">
                        <a href="{{ route('site.event.show', [$fe->evento_id, $slug]) }}" class="d-block text-decoration-none text-reset">
                            <div class="ratio ratio-16x9" style="overflow:hidden;">
                                <img src="{{ $cover }}" class="d-block w-100 h-100" style="object-fit:cover;object-position:center;background-color:#000;" alt="{{ $nome }}">
                            </div>
                            <div class="carousel-caption d-none d-md-block" style="background:linear-gradient(180deg, rgba(0,0,0,0.55), rgba(0,0,0,0)); border-radius:.5rem;">
                                <h5>{{ $nome }}</h5>
                                @if($when || $where)
                                    <p>{{ $when }}@if($when && $where) • @endif{{ $where }}</p>
                                @endif
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#eventPromoCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#eventPromoCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Próximo</span>
            </button>
        </div>
    </section>
    @endif
    
<!-- Estabelecimetnos -->

    <section class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4 fw-bold mb-0">Estabelecimentos</h2>
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('site.establishments.index') }}">Ver todos</a>
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
        <!-- Próximos eventos -->

<section class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4 fw-bold mb-0">
                @if(request()->filled('q'))
                    Eventos encontrados
                @else
                    Próximos eventos
                @endif
            </h2>
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('site.events.index') }}">Ver todos</a>
        </div>
        @if(empty($events) || $events->isEmpty())
            <p class="text-muted small mb-0">Nenhum evento encontrado no momento.</p>
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
        @endif
    </section>
    <!-- Produtos -->
    @if(!empty($products) && $products->isNotEmpty())
    <section class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4 fw-bold mb-0">Produtos</h2>
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('site.products.index') }}">Ver todos</a>
        </div>
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
    </section>
    @endif


<!-- ingressos -->
    <section class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4 fw-bold mb-0">
                @if(request()->filled('q'))
                    Ingressos encontrados
                @else
                    Ingressos em destaque
                @endif
            </h2>
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('site.tickets.index') }}">Ver todos</a>
        </div>
        @if(empty($tickets) || $tickets->isEmpty())
            <p class="text-muted small mb-0">Nenhum ingresso encontrado.</p>
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
        @endif
    </section>

    
<div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filtros de busca</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="filterForm" action="{{ url('/') }}" method="GET">
                    <input type="hidden" name="q" value="{{ request('q') }}">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="filterEstado" class="form-label">Estado</label>
                            <select name="estado_id" id="filterEstado" class="form-select">
                                <option value="">Selecione</option>
                                @foreach(($estados ?? collect()) as $estado)
                                    <option value="{{ $estado->id }}" @selected((int)request('estado_id') === (int)$estado->id)>
                                        {{ $estado->uf }} - {{ $estado->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            @php
                                $cidadesLista = ($cidades ?? collect());
                                $cidadesDisabled = !($cidadesLista instanceof \Illuminate\Support\Collection) || $cidadesLista->isEmpty();
                            @endphp
                            <label for="filterCidade" class="form-label">Cidade</label>
                            <select name="cidade_id" id="filterCidade" class="form-select" @if($cidadesDisabled) disabled @endif>
                                <option value="">Selecione</option>
                                @foreach($cidadesLista as $cidade)
                                    <option value="{{ $cidade->id }}" @selected((int)request('cidade_id') === (int)$cidade->id)>
                                        {{ $cidade->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-8">
                            @php
                                $bairrosLista = ($bairros ?? collect());
                                $bairrosDisabled = !($bairrosLista instanceof \Illuminate\Support\Collection) || $bairrosLista->isEmpty();
                            @endphp
                            <label for="filterBairro" class="form-label">Bairro</label>
                            <select name="bairro_id" id="filterBairro" class="form-select" @if($bairrosDisabled) disabled @endif>
                                <option value="">Selecione</option>
                                @foreach($bairrosLista as $bairro)
                                    <option value="{{ $bairro->id }}" @selected((int)request('bairro_id') === (int)$bairro->id)>
                                        {{ $bairro->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-end">
                            @auth
                            <button type="button" class="btn btn-outline-secondary w-100" id="btnNovoBairro">
                                Novo bairro
                            </button>
                            @endauth
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="filterTipoBar" class="form-label">Tipo de estabelecimento</label>
                            <select name="tipo_bar_id" id="filterTipoBar" class="form-select">
                                <option value="">Selecione</option>
                                @foreach(($tipoEstabelecimentos ?? collect()) as $tipo)
                                    <option value="{{ $tipo->tipo_bar_id }}" @selected((int)request('tipo_bar_id') === (int)$tipo->tipo_bar_id)>
                                        {{ $tipo->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="filterTipoEvento" class="form-label">Tipo de evento</label>
                            <select name="tipo_evento_id" id="filterTipoEvento" class="form-select">
                                <option value="">Selecione</option>
                                @foreach(($tiposEvento ?? collect()) as $tipo)
                                    <option value="{{ $tipo->tipo_evento_id }}" @selected((int)request('tipo_evento_id') === (int)$tipo->tipo_evento_id)>
                                        {{ $tipo->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="submit" form="filterForm" class="btn btn-primary">Aplicar filtros</button>
            </div>
        </div>
    </div>
</div>
@auth
<div class="modal fade" id="modalNovoBairro" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Novo bairro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formNovoBairro" class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Cidade selecionada</label>
                        <input type="text" id="novoBairroCidadeNome" class="form-control" disabled>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Nome do bairro</label>
                        <input type="text" name="nome" id="novoBairroNome" class="form-control">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarNovoBairro">Salvar</button>
            </div>
        </div>
    </div>
</div>
@endauth
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var cidadesCache = {};
    var bairrosCache = {};
    var estado = document.getElementById('filterEstado');
    var cidade = document.getElementById('filterCidade');
    var bairro = document.getElementById('filterBairro');
    var btnNovoBairro = document.getElementById('btnNovoBairro');
    var modalEl = document.getElementById('modalNovoBairro');
    var cidadeNomeInput = document.getElementById('novoBairroCidadeNome');
    var bairroNomeInput = document.getElementById('novoBairroNome');
    var btnSalvarNovoBairro = document.getElementById('btnSalvarNovoBairro');
    if (estado && cidade) {
        estado.addEventListener('change', function () {
            var id = this.value;
            cidade.innerHTML = '<option value=\"\">Cidade</option>';
            bairro.innerHTML = '<option value=\"\">Bairro</option>';
            cidade.disabled = true;
            bairro.disabled = true;
            if (!id) {
                return;
            }
            var cachedCidades = cidadesCache[id];
            if (cachedCidades) {
                cachedCidades.forEach(function (c) {
                    var optCached = document.createElement('option');
                    optCached.value = c.id;
                    optCached.textContent = c.nome;
                    cidade.appendChild(optCached);
                });
                cidade.disabled = false;
                return;
            }
            fetch("{{ route('api.geo.cidades.bares', ['estadoId' => 'ESTADO_ID']) }}".replace('ESTADO_ID', id))
                .then(function (r) { return r.json(); })
                .then(function (rows) {
                    cidadesCache[id] = rows.slice();
                    rows.forEach(function (c) {
                        var opt = document.createElement('option');
                        opt.value = c.id;
                        opt.textContent = c.nome;
                        cidade.appendChild(opt);
                    });
                    cidade.disabled = false;
                })
                .catch(function () {});
        });
    }
    if (cidade && bairro) {
        cidade.addEventListener('change', function () {
            var id = this.value;
            bairro.innerHTML = '<option value=\"\">Bairro</option>';
            bairro.disabled = true;
            if (!id) {
                return;
            }
            var cachedBairros = bairrosCache[id];
            if (cachedBairros) {
                cachedBairros.forEach(function (b) {
                    var optCached = document.createElement('option');
                    optCached.value = b.id;
                    optCached.textContent = b.nome;
                    bairro.appendChild(optCached);
                });
                bairro.disabled = false;
                return;
            }
            fetch("{{ route('api.geo.bairros.bares', ['cidadeId' => 'CIDADE_ID']) }}".replace('CIDADE_ID', id))
                .then(function (r) { return r.json(); })
                .then(function (rows) {
                    bairrosCache[id] = rows.slice();
                    rows.forEach(function (b) {
                        var opt = document.createElement('option');
                        opt.value = b.id;
                        opt.textContent = b.nome;
                        bairro.appendChild(opt);
                    });
                    bairro.disabled = false;
                })
                .catch(function () {});
        });
    }
    if (btnNovoBairro && modalEl && cidade && cidadeNomeInput && bairro && bairroNomeInput && btnSalvarNovoBairro) {
        btnNovoBairro.addEventListener('click', function () {
            var cidadeOption = cidade.options[cidade.selectedIndex];
            if (!cidade.value || !cidadeOption) {
                alert('Selecione uma cidade antes de cadastrar o bairro.');
                return;
            }
            cidadeNomeInput.value = cidadeOption.textContent;
            bairroNomeInput.value = '';
            var modal = new bootstrap.Modal(modalEl);
            modal.show();
        });
        btnSalvarNovoBairro.addEventListener('click', function () {
            var nome = bairroNomeInput.value.trim();
            var cidadeId = cidade.value;
            if (!nome || !cidadeId) {
                alert('Informe o nome do bairro.');
                return;
            }
            var csrf = document.querySelector('meta[name=\"csrf-token\"]');
            var token = csrf ? csrf.getAttribute('content') : '';
            var formData = new FormData();
            formData.append('cidade_id', cidadeId);
            formData.append('nome', nome);
            fetch('{{ route('api.geo.bairros.store') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token
                },
                body: formData
            }).then(function (r) {
                if (!r.ok) {
                    throw new Error('Erro ao salvar bairro');
                }
                return r.json();
            }).then(function (data) {
                var opt = document.createElement('option');
                opt.value = data.id;
                opt.textContent = data.nome;
                bairro.appendChild(opt);
                bairro.value = data.id;
                bairro.disabled = false;
                var modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
            }).catch(function () {
                alert('Não foi possível cadastrar o bairro agora.');
            });
        });
    }
});
</script>
@endpush
