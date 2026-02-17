<section class="mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="h6 fw-bold mb-0">Stories dos Roles</h2>
        <span class="text-muted small">Últimas 24h</span>
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
</section>

@push('scripts')
<script>
let allStories = [];
let currentStoryIndex = 0;

function storyItem(s, index){
    const img = s.image_url ? ('/' + encodeURI(s.image_url)) : 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=200';
    const name = s.name || s.user_name || 'Story';
    return '<button class="border-0 bg-transparent p-0 text-center" onclick="openStoryModal('+index+')" type="button">'
        + '<div class="rounded-circle p-1 shadow" style="background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);">'
        + '  <div class="rounded-circle border border-3 border-white" style="width:84px;height:84px;overflow:hidden;">'
        + '    <img src="'+img+'" alt="'+name+'" class="w-100 h-100" style="object-fit:cover;">'
        + '  </div>'
        + '</div>'
        + '<small class="d-block mt-2 text-truncate" style="max-width:96px;">'+name+'</small>'
        + '</button>';
}

function openStoryModal(index) {
    if (!allStories || !allStories.length) return;
    currentStoryIndex = index;
    const story = allStories[index];
    const imgUrl = story.image_url ? ('/' + encodeURI(story.image_url)) : '';
    const img = document.getElementById('storyModalImage');
    if (img && imgUrl) {
        img.src = imgUrl;
        const modalEl = document.getElementById('storyModal');
        if (modalEl && window.bootstrap && bootstrap.Modal) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    }
}

function changeStory(direction) {
    if (!allStories || !allStories.length) return;
    currentStoryIndex = (currentStoryIndex + direction + allStories.length) % allStories.length;
    const story = allStories[currentStoryIndex];
    const imgUrl = story.image_url ? ('/' + encodeURI(story.image_url)) : '';
    const img = document.getElementById('storyModalImage');
    if (img && imgUrl) {
        img.src = imgUrl;
    }
}

async function loadStories(){
    const items = document.getElementById('storiesItems');
    if (!items) return;
    items.innerHTML = '<div class="text-muted small">Carregando...</div>';
    try{
        const limit = 12;
        const r = await fetch('{{ url('/home/stories-all') }}?limit='+limit+'&_ts='+Date.now(), {
            headers:{
                'Accept':'application/json',
                'X-Requested-With':'XMLHttpRequest'
            }
        });
        if(!r.ok){
            items.innerHTML = '<div class="text-danger small">Erro ao carregar stories</div>';
            return;
        }
        const data = await r.json();
        if(!Array.isArray(data) || !data.length){
            items.innerHTML = '<div class="text-muted small">Sem stories por enquanto</div>';
            return;
        }
        allStories = data;
        items.innerHTML = data.map(function(s, i){ return storyItem(s, i); }).join('');
    }catch(e){
        console.error(e);
        if (items) items.innerHTML = '<div class="text-muted small">Falha de rede</div>';
    }
}

function initStoriesScroll(){
    const scrollEl = document.getElementById('storiesScroll');
    const prevBtn = document.getElementById('storiesPrev');
    const nextBtn = document.getElementById('storiesNext');
    if (!scrollEl || !prevBtn || !nextBtn) return;
    const step = 160;
    prevBtn.addEventListener('click', function(){ scrollEl.scrollBy({left:-step, behavior:'smooth'}); });
    nextBtn.addEventListener('click', function(){ scrollEl.scrollBy({left:step, behavior:'smooth'}); });
}

document.addEventListener('DOMContentLoaded', function () {
    loadStories();
    initStoriesScroll();
});
</script>

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
            </div>
        </div>
    </div>
</div>
@endpush

