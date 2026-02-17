@extends('_layout.site.site_default')

@section('content')
@php
    $eventCover = $event && $event->imagem_capa ? asset(ltrim($event->imagem_capa, '/')) : 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1400&h=700&fit=crop';
    $ticketTitle = $ticket->nome ?? 'Ingresso';
    $eventName = $event?->nome ?? '';
    $metaTitle = $eventName !== '' ? $ticketTitle.' | '.$eventName.' | Ingresso | RolesBr' : $ticketTitle.' | Ingresso | RolesBr';
    $metaDescBase = trim((string)($event->descricao ?? ''));
    if ($metaDescBase === '') {
        $metaDescBase = 'Detalhes do ingresso no RolesBr.';
    }
    $metaDesc = \Illuminate\Support\Str::limit($metaDescBase, 160, '...');
    $likesPost = \Illuminate\Support\Facades\DB::table('user_posts_tb')
        ->where('posted_as_type', 'ticket')
        ->where('posted_as_id', $ticket->lote_id)
        ->first();
    $likesCount = $likesPost->likes_count ?? 0;
    $likedByMe = false;
    if (auth()->check() && $likesPost) {
        $likedByMe = \Illuminate\Support\Facades\DB::table('user_post_likes_tb')
            ->where('user_id', auth()->id())
            ->where('post_id', $likesPost->post_id)
            ->exists();
    }
@endphp
@section('meta_title', $metaTitle)
@section('meta_description', $metaDesc)
@section('meta_image', $eventCover)
@section('meta_og_type', 'product')
<div class="event-container">
    <div class="hero-section">
        <div class="hero-image" style="background-image: url('{{ $eventCover }}');">
            <div class="hero-overlay">
                <div class="hero-content">
                    <h1 class="hero-title">{{ $event?->nome }}</h1>
                    <div class="hero-meta">
                        @if($event && $event->data_inicio)
                            <div class="event-date">
                                <i class="fas fa-calendar-alt"></i>
                                {{ \Carbon\Carbon::parse($event->data_inicio)->translatedFormat('d \\d\\e F, H:i') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="main-content">
        <div class="content-grid">
            <div class="main-column">
                <div class="tickets-section">
                    <div class="section-header d-flex justify-content-between align-items-center">
                        <h2 class="section-title">
                            <i class="fas fa-ticket-alt"></i>
                            Ingresso selecionado
                        </h2>
                        <button type="button"
                                class="btn btn-outline-danger btn-sm like-toggle"
                                data-type="ticket"
                                data-id="{{ $ticket->lote_id }}"
                                data-liked="{{ $likedByMe ? 1 : 0 }}">
                            <i class="{{ $likedByMe ? 'fas' : 'far' }} fa-heart me-1"></i>
                            <span class="like-count">{{ $likesCount }}</span>
                        </button>
                    </div>
                    <div class="tickets-grid">
                        <div class="ticket-card">
                            <div class="ticket-info">
                                <h3 class="ticket-name">{{ $ticket->nome }}</h3>
                                <div class="ticket-type">{{ $ticket->tipo }}</div>
                            </div>
                            <div class="ticket-price">
                                <div class="price">R$ {{ number_format($ticket->preco, 2, ',', '.') }}</div>
                            </div>
                            <div class="ticket-actions">
                                <a href="{{ route('site.ticket.checkout', $ticket->lote_id) }}" class="btn-ticket primary">
                                    <i class="fas fa-shopping-cart"></i>
                                    Comprar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @if($event && $event->descricao)
                    <div class="event-details">
                        <div class="description">
                            {{ $event->descricao }}
                        </div>
                    </div>
                @endif
            </div>
            <div class="sidebar">
                @if($event && $event->establishment)
                <div class="establishment-card w-100">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-map-pin"></i>
                            Local do evento
                        </h3>
                    </div>
                    <div class="establishment-info">
                        <h4 class="establishment-name">{{ $event->establishment->nome }}</h4>
                        <div class="address">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>{{ $event->establishment->endereco }}</span>
                        </div>
                        <div class="location-details">
                            <span class="neighborhood">{{ $event->establishment->bairro_nome }}</span>
                            @if($event->establishment->cidade_nome)
                                <span class="separator">•</span>
                                <span class="city">{{ $event->establishment->cidade_nome }}</span>
                            @endif
                        </div>
                        <a href="{{ route('site.establishment.show', [$event->establishment->bares_id, \Illuminate\Support\Str::slug($event->establishment->nome)]) }}" class="btn-establishment">
                            Ver estabelecimento
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.event-container{width:100%;margin:0;padding:0;background:#020617;color:#e5e7eb;}
.hero-section{margin:0!important;border-radius:0;overflow:hidden;box-shadow:none;}
.hero-image{height:360px;background-size:cover;background-position:center;position:relative;}
.hero-overlay{background:linear-gradient(135deg,rgba(0,0,0,0.85) 0%,rgba(0,0,0,0.6) 60%,rgba(0,0,0,0.2) 100%);height:100%;display:flex;align-items:flex-end;padding:2.5rem 1.5rem;}
.hero-title{font-size:clamp(1.8rem,4vw,3rem);font-weight:800;background:linear-gradient(135deg,#fff 0%,#facc15 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin:0 0 .75rem 0;line-height:1.1;}
.hero-meta .event-date{display:flex;align-items:center;gap:.5rem;color:#e5e7eb;font-size:1rem;font-weight:500;}
.main-content{background:#020617;}
.content-grid{display:grid;grid-template-columns:minmax(0,2.2fr) minmax(0,1fr);gap:1.5rem;padding:1.5rem;}
.main-column{display:flex;flex-direction:column;gap:1rem;}
.sidebar{display:flex;flex-direction:column;gap:1rem;width:100%;}
.tickets-section{background:#020617;border-radius:14px;border:1px solid rgba(148,163,184,0.5);box-shadow:0 18px 40px rgba(0,0,0,0.4);padding:1.5rem;}
.section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;}
.section-title{display:flex;align-items:center;gap:.5rem;font-size:.9rem;letter-spacing:.12em;text-transform:uppercase;color:#facc15;margin:0;}
.tickets-grid{display:grid;grid-template-columns:1fr;gap:1rem;}
.ticket-card{border-radius:12px;border:1px solid rgba(55,65,81,0.9);background:radial-gradient(circle at top left,rgba(250,204,21,0.08) 0,rgba(15,23,42,1) 45%);padding:1rem 1.25rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;}
.ticket-info{flex:1 1 auto;}
.ticket-name{margin:0 0 .25rem 0;font-size:1rem;font-weight:600;color:#f9fafb;}
.ticket-type{font-size:.8rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;}
.ticket-price .price{font-weight:700;font-size:1.1rem;color:#facc15;}
.ticket-actions{display:flex;flex-direction:column;gap:.4rem;}
.btn-ticket{display:inline-flex;align-items:center;justify-content:center;gap:.35rem;padding:.45rem .85rem;border-radius:999px;font-size:.8rem;font-weight:600;text-decoration:none;transition:.2s all;border:1px solid transparent;white-space:nowrap;}
.btn-ticket.primary{background:#facc15;color:#111827;border-color:#facc15;}
.btn-ticket.primary:hover{filter:brightness(.94);color:#020617;}
.establishment-card{border-radius:14px;border:1px solid rgba(55,65,81,0.9);background:linear-gradient(160deg,rgba(15,23,42,1) 0%,rgba(17,24,39,1) 60%,rgba(15,23,42,1) 100%);box-shadow:0 18px 40px rgba(0,0,0,0.45);overflow:hidden;}
.establishment-card .card-header{padding:.85rem 1rem;border-bottom:1px solid rgba(55,65,81,0.9);}
.establishment-card .card-title{margin:0;font-size:.85rem;letter-spacing:.12em;text-transform:uppercase;color:#9ca3af;display:flex;align-items:center;gap:.35rem;}
.establishment-info{padding:1rem;}
.establishment-name{margin:0 0 .35rem 0;font-size:1rem;font-weight:600;color:#f9fafb;}
.address,.location-details{font-size:.85rem;color:#9ca3af;display:flex;align-items:center;gap:.4rem;}
.btn-establishment{margin-top:.75rem;display:inline-flex;align-items:center;justify-content:center;padding:.45rem .9rem;border-radius:999px;background:transparent;border:1px solid rgba(250,204,21,0.8);color:#facc15;font-size:.8rem;font-weight:600;text-decoration:none;transition:.2s all;}
.btn-establishment:hover{background:#facc15;color:#111827;}
.event-details{margin-top:1rem;background:transparent;border-radius:14px;border:1px solid rgba(55,65,81,0.9);padding:1.25rem;color:#e5e7eb;}
.description{font-size:.95rem;line-height:1.7;color:#d1d5db;}
@media (max-width:992px){.content-grid{grid-template-columns:1fr;padding:1rem;}.hero-image{height:260px;}.ticket-card{flex-direction:column;align-items:flex-start;}.sidebar{width:100%;}}
/* Mobile full-bleed (estilo Instagram) */
@media (max-width:767.98px){
  .content-grid{padding:0 !important;}
  .tickets-section{padding-left:0 !important;padding-right:0 !important;border:0 !important;border-radius:0 !important;}
  .event-details{padding-left:0 !important;padding-right:0 !important;border:0 !important;border-radius:0 !important;}
}

.voucher-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.65);
    display: none; align-items: center; justify-content: center;
    z-index: 1050;
}
.voucher-overlay.show { display: flex; }
.voucher-modal {
    background: #fff; width: 95%; max-width: 760px; border-radius: 12px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.25); overflow: hidden;
}
.voucher-header { padding: 1rem 1.25rem; border-bottom: 1px solid #e5e7eb; display:flex; justify-content: space-between; align-items: center;}
.voucher-title { margin: 0; font-size: 1.1rem; font-weight: 700; }
.voucher-body { padding: 1rem 1.25rem; }
.voucher-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
.voucher-card { border: 1px solid #e5e7eb; border-radius: 10px; padding: 0.75rem; display: grid; grid-template-columns: 100px 1fr; gap: 0.75rem; align-items: center; }
.voucher-qr img { width: 100px; height: 100px; object-fit: contain; }
.voucher-meta { font-size: 0.9rem; }
.voucher-meta .code { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; font-weight: 700; }
.voucher-footer { padding: 0.75rem 1.25rem; border-top: 1px solid #e5e7eb; display:flex; gap: 0.5rem; justify-content: flex-end; }
@media (max-width: 600px) {
  .voucher-grid { grid-template-columns: 1fr; }
  .voucher-card { grid-template-columns: 80px 1fr; }
  .voucher-qr img { width: 80px; height: 80px; }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const voucherData = @json(session('voucher'));
    if (!voucherData) return;
    const overlay = document.createElement('div');
    overlay.className = 'voucher-overlay show';
    overlay.innerHTML = `
      <div class="voucher-modal">
        <div class="voucher-header">
          <h3 class="voucher-title">Comprovante da compra</h3>
          <button type="button" class="btn btn-sm btn-outline-secondary" id="closeVoucher">Fechar</button>
        </div>
        <div class="voucher-body">
          <div class="mb-2">
            <div><strong>Evento:</strong> ${voucherData.evento_nome} • <strong>Lote:</strong> ${voucherData.lote_nome}</div>
            <div class="text-muted small">Titular: ${voucherData.titular_nome}</div>
          </div>
          <div class="voucher-grid" id="voucherGrid"></div>
        </div>
        <div class="voucher-footer">
          <a class="btn btn-outline-secondary" id="btnDownloadAll" href="#" download>Baixar QR (abrir imagens)</a>
          <a class="btn btn-primary" id="btnWhats" target="_blank">Enviar WhatsApp</a>
        </div>
      </div>
    `;
    document.body.appendChild(overlay);
    const grid = overlay.querySelector('#voucherGrid');
    const baseMsg = `Ingresso - ${voucherData.evento_nome}`;
    let whatsText = `${baseMsg}%0A`;
    voucherData.codes.forEach((c, idx) => {
      const code = c.codigo;
      const displayName = c.nome || voucherData.titular_nome;
      const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(code)}`;
      const card = document.createElement('div');
      card.className = 'voucher-card';
      card.innerHTML = `
        <div class="voucher-qr">
          <img src="${qrUrl}" alt="QR Code ${code}">
        </div>
        <div class="voucher-meta">
          <div><strong>${idx === 0 && c.is_titular ? 'Titular' : 'Convidado'}:</strong> ${displayName}</div>
          <div>Código: <span class="code">${code}</span></div>
        </div>
      `;
      grid.appendChild(card);
      whatsText += `• ${displayName} — Código: ${code}%0A`;
    });
    const whatsBtn = overlay.querySelector('#btnWhats');
    whatsBtn.href = `https://wa.me/?text=${whatsText}`;
    const closeBtn = overlay.querySelector('#closeVoucher');
    closeBtn.addEventListener('click', ()=> overlay.remove());
    overlay.addEventListener('click', (e)=> { if (e.target === overlay) overlay.remove(); });
    const dlBtn = overlay.querySelector('#btnDownloadAll');
    dlBtn.addEventListener('click', function(e){
      e.preventDefault();
      // Abre cada QR em aba nova para permitir salvar a imagem
      overlay.querySelectorAll('.voucher-qr img').forEach(img => {
        window.open(img.src, '_blank');
      });
    });
});
</script>
@endpush

@push('scripts')
<script>
document.addEventListener('click', function (e) {
    var btn = e.target.closest('.like-toggle');
    if (!btn) return;
    var type = btn.getAttribute('data-type');
    var id = btn.getAttribute('data-id');
    if (!type || !id) return;
    fetch("{{ route('api.likes.toggle') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ type: type, id: parseInt(id, 10) })
    }).then(function (r) {
        if (r.status === 401) {
            window.location.href = "{{ route('login') }}";
            return null;
        }
        return r.json();
    }).then(function (data) {
        if (!data) return;
        if (data.error === 'not_implemented') {
            alert('Curtidas para este tipo ainda não estão disponíveis.');
            return;
        }
        if (typeof data.liked !== 'undefined') {
            btn.setAttribute('data-liked', data.liked ? '1' : '0');
            var icon = btn.querySelector('i');
            if (icon) {
                icon.classList.toggle('fas', data.liked);
                icon.classList.toggle('far', !data.liked);
            }
        }
        if (typeof data.likes !== 'undefined') {
            var span = btn.querySelector('.like-count');
            if (span) span.textContent = data.likes;
        }
    }).catch(function(){});
});
</script>
@endpush
