@extends('_layout.dashboard.rolezeiro.layout_rolezeiro')

@section('title', 'Stories do Perfil')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12 col-lg-4 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle overflow-hidden" style="width:72px;height:72px;background:#e5e7eb;">
                        @php
                            $foto = $perfil->foto_perfil ?? null;
                        @endphp
                        @if($foto)
                            <img src="{{ asset(ltrim($foto, '/')) }}" alt="Perfil" class="w-100 h-100" style="object-fit:cover;">
                        @else
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                                <i class="fas fa-user fa-lg"></i>
                            </div>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold mb-1">{{ auth()->user()->name ?? 'Usuário' }}</div>
                        <div class="small text-muted">
                            Stories ativos por 24 horas após a publicação.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-8 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h1 class="h5 fw-bold mb-3">Criação de Stories</h1>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <a href="{{ url('/stories') }}" class="btn btn-primary btn-sm">Explorar público</a>
                        <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-sm">Voltar para o site</a>
                    </div>
                    @if(session('status'))
                        <div class="alert alert-success mt-3 mb-3">{{ session('status') }}</div>
                    @endif
                    <form class="mt-2" method="post" action="{{ route('dashboard.stories.upload') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-2 align-items-center">
                            <div class="col-12 col-md-4">
                                <input type="file" name="image" class="form-control form-control-sm" accept="image/*" required>
                            </div>
                            <div class="col-12 col-md-auto">
                                <button type="submit" class="btn btn-sm btn-success">Publicar story</button>
                            </div>
                        </div>
                        @error('image')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </form>
                    <div id="editorPreview"></div>
                    <hr class="my-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="h6 fw-bold mb-0">Meus Stories</h2>
                    </div>
                    @if(!empty($myStories) && $myStories->isNotEmpty())
                        <div class="row g-3">
                            @foreach($myStories as $s)
                                @php
                                    $img = $s->image_url ? asset(ltrim($s->image_url,'/')) : null;
                                @endphp
                                <div class="col-6 col-md-4">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="ratio ratio-1x1">
                                            @if($img)
                                                <img src="{{ $img }}" alt="Story" class="w-100 h-100" style="object-fit:cover;">
                                            @else
                                                <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center text-muted">Sem imagem</div>
                                            @endif
                                        </div>
                                        <div class="card-body py-2 d-flex justify-content-between align-items-center">
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($s->created_at)->diffForHumans() }}</small>
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-delete-story="{{ $s->story_id }}">Excluir</button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">Você ainda não publicou stories.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @if(!empty($ads))
        <div class="row mt-3">
            @foreach($ads as $ad)
                @php
                    $nomeBar = $ad->nome ?? 'Bar parceiro';
                    $imgBar = $ad->imagem ? asset(ltrim($ad->imagem, '/')) : 'https://images.unsplash.com/photo-1514933651103-005eec06c04b?w=600&h=400&fit=crop';
                @endphp
                <div class="col-12 col-md-4 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <img src="{{ $imgBar }}" alt="{{ $nomeBar }}" class="card-img-top" style="object-fit:cover;height:140px;">
                        <div class="card-body py-2">
                            <div class="small fw-semibold mb-1 text-truncate">{{ $nomeBar }}</div>
                            <div class="small text-muted">Publicidade de estabelecimentos parceiros.</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function(){
    const fileInput = document.querySelector('input[name="image"]');
    if (!fileInput) return;
    let overlay = { elements: [], mentions: [], music: null };
    const form = fileInput.closest('form');
    const container = document.getElementById('editorPreview');
    const panel = document.createElement('div');
    panel.className = 'mt-3';
    panel.innerHTML = `
      <div class="border rounded p-2">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-add-text>Texto</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-add-sticker>Sticker</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-add-mention>Mencionar</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-add-music>Música</button>
          </div>
          <small class="text-muted">Pré-visualização</small>
        </div>
        <div class="ratio ratio-9x16 bg-light position-relative" id="storyCanvasBox" style="max-width:320px;margin:0 auto;overflow:hidden;">
          <img id="storyBaseImg" src="" alt="Preview" class="position-absolute top-0 start-0 w-100 h-100" style="object-fit:cover; display:none;">
          <div id="storyOverlay" class="position-absolute top-0 start-0 w-100 h-100"></div>
        </div>
        <audio id="storyMusic" controls class="mt-2 w-100 d-none"></audio>
      </div>
    `;
    container.appendChild(panel);

    function refreshOverlay() {
        const box = document.getElementById('storyOverlay');
        if (!box) return;
        box.innerHTML = '';
        overlay.elements.forEach((el, idx) => {
            const div = document.createElement('div');
            div.className = 'position-absolute';
            div.style.left = (el.x || 50) + '%';
            div.style.top = (el.y || 50) + '%';
            div.style.transform = `translate(-50%,-50%) scale(${el.scale || 1})`;
            div.style.color = el.fill || '#fff';
            div.style.fontWeight = '700';
            div.style.textShadow = '0 1px 3px rgba(0,0,0,0.6)';
            div.style.cursor = 'move';
            div.setAttribute('draggable','true');
            div.addEventListener('dragstart', function(e){
                e.dataTransfer.setData('text/plain', idx.toString());
            });
            if (el.type === 'text') {
                div.textContent = el.text || 'Texto';
            } else if (el.type === 'sticker') {
                div.textContent = el.text || '✨';
                div.style.fontSize = '1.8rem';
            } else if (el.type === 'mention') {
                div.textContent = '@'+(el.name || (el.mention_type||'')+'-'+(el.mention_id||'')); 
                div.style.background = 'rgba(0,0,0,0.5)';
                div.style.padding = '2px 6px';
                div.style.borderRadius = '12px';
            }
            box.appendChild(div);
        });
    }
    document.getElementById('storyOverlay')?.addEventListener('dragover', function(e){
        e.preventDefault();
    });
    document.getElementById('storyOverlay')?.addEventListener('drop', function(e){
        e.preventDefault();
        const idx = parseInt(e.dataTransfer.getData('text/plain'), 10);
        if (isNaN(idx) || !overlay.elements[idx]) return;
        const rect = this.getBoundingClientRect();
        const x = ((e.clientX - rect.left) / rect.width) * 100;
        const y = ((e.clientY - rect.top) / rect.height) * 100;
        overlay.elements[idx].x = Math.max(0, Math.min(100, x));
        overlay.elements[idx].y = Math.max(0, Math.min(100, y));
        refreshOverlay();
    });

    fileInput.addEventListener('change', function(){
        const f = this.files && this.files[0];
        const img = document.getElementById('storyBaseImg');
        if (f && img) {
            img.src = URL.createObjectURL(f);
            img.style.display = '';
        }
    });
    panel.querySelector('[data-add-text]')?.addEventListener('click', function(){
        const txt = prompt('Digite o texto:','Meu story!');
        if (!txt) return;
        overlay.elements.push({ type:'text', text:txt, x:50, y:50, scale:1, fill:'#ffffff' });
        refreshOverlay();
    });
    panel.querySelector('[data-add-sticker]')?.addEventListener('click', function(){
        const st = prompt('Sticker (ex: 😀, 🎉, ❤️):','🎉');
        if (!st) return;
        overlay.elements.push({ type:'sticker', text:st, x:50, y:40, scale:1.2 });
        refreshOverlay();
    });
    panel.querySelector('[data-add-mention]')?.addEventListener('click', async function(){
        const type = prompt('Mencionar (user ou bar):','user');
        if (!type) return;
        const q = prompt('Buscar por nome:','');
        const url = '{{ route('api.stories.mentions') }}' + '?type=' + encodeURIComponent(type) + '&q=' + encodeURIComponent(q||'');
        const r = await fetch(url, { headers: { 'Accept':'application/json' }});
        const data = await r.json();
        const item = (data.items && data.items[0]) || null;
        if (!item) { alert('Nada encontrado'); return; }
        overlay.elements.push({ type:'mention', mention_type:type, mention_id:item.id, name:(item.nome||item.name||'') , x:50, y:60, scale:1 });
        overlay.mentions = overlay.elements.filter(e => e.type === 'mention');
        refreshOverlay();
    });
    panel.querySelector('[data-add-music]')?.addEventListener('click', async function(){
        const q = prompt('Buscar música (vazio para lista):','');
        const url = '{{ route('api.stories.music') }}' + (q ? ('?q=' + encodeURIComponent(q)) : '');
        const r = await fetch(url, { headers: {'Accept':'application/json'} });
        const list = await r.json();
        const pick = list[0];
        if (!pick) { alert('Sem resultados'); return; }
        overlay.music = { id: pick.id, title: pick.title, artist: pick.artist, url: pick.url, offset: 0 };
        const audio = document.getElementById('storyMusic');
        if (audio) {
            audio.src = pick.url;
            audio.classList.remove('d-none');
            audio.play().catch(()=>{});
        }
    });

    form.addEventListener('submit', function(){
        let input = form.querySelector('input[name="overlay_json"]');
        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'overlay_json';
            form.appendChild(input);
        }
        input.value = JSON.stringify(overlay);
    });

    document.addEventListener('click', async function(e){
        const btn = e.target.closest('[data-delete-story]');
        if (!btn) return;
        const id = btn.getAttribute('data-delete-story');
        if (!id) return;
        if (!confirm('Excluir este story?')) return;
        const r = await fetch('{{ route('dashboard.stories.destroy', ['story' => 0]) }}'.replace('/0','/'+id), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        if (r.ok) {
            location.reload();
        } else {
            alert('Falha ao excluir');
        }
    });
})();
</script>
@endpush
