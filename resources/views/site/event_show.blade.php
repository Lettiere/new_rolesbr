@extends('_layout.site.site_default')

@section('content')
@php
    $cover = $event->imagem_capa ? asset(ltrim($event->imagem_capa, '/')) : 'https://images.unsplash.com/photo-1514933651103-005eec06c04b?w=1400&h=700&fit=crop';
@endphp
<div class="event-container">
    <div class="hero-section">
        <div class="hero-image" style="background-image: url('{{ $cover }}');">
            <div class="hero-overlay">
                <div class="hero-content">
                    <h1 class="hero-title">{{ $event->nome }}</h1>
                    <div class="hero-meta">
                        @if($event->data_inicio)
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
                $wazeLink = ($lat && $lon) ? ('https://waze.com/ul?ll=' . $lat . ',' . $lon . '&navigate=yes') : ('https://waze.com/ul?q=' . urlencode($address) . '&navigate=yes');
                $shareTitle = trim($event->nome . ' - ' . ($event->establishment->nome ?? ''));
                $currentUrl = url()->current();
            @endphp
    <div class="main-content">
        <div class="content-grid">
            <div class="main-column">
                @if($event->descricao)
                    <div class="event-details">
                        <div class="description">
                            {{ $event->descricao }}
                        </div>
                        <div class="action-buttons">
                            <a href="{{ $mapsLink }}" target="_blank" rel="noopener" class="btn-action maps-btn" data-tooltip="Abrir no Google Maps">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>Maps</span>
                            </a>
                            <a href="{{ $wazeLink }}" target="_blank" rel="noopener" class="btn-action waze-btn" data-tooltip="Abrir no Waze">
                                <i class="fas fa-route"></i>
                                <span>Waze</span>
                            </a>
                            <a href="https://wa.me/?text={{ urlencode($shareTitle.' '.$currentUrl) }}" target="_blank" rel="noopener" class="btn-action whatsapp-btn" data-tooltip="Compartilhar no WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                                <span>WhatsApp</span>
                            </a>
                        </div>
                    </div>
                @endif
                @if(!$lots->isEmpty())
                <div class="tickets-section">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-ticket-alt"></i>
                            Ingressos disponíveis
                        </h2>
                    </div>
                    <div class="tickets-grid">
                        @foreach($lots as $lot)
                        <div class="ticket-card">
                            <div class="ticket-info">
                                <h3 class="ticket-name">{{ $lot->nome }}</h3>
                                <div class="ticket-type">{{ $lot->tipo }}</div>
                            </div>
                            <div class="ticket-price">
                                <div class="price">R$ {{ number_format($lot->preco, 2, ',', '.') }}</div>
                            </div>
                            <div class="ticket-actions">
                                <a href="{{ route('site.ticket.show', $lot->lote_id) }}" class="btn-ticket secondary">
                                    <i class="fas fa-eye"></i>
                                    Ver
                                </a>
                                <a href="{{ route('site.ticket.checkout', $lot->lote_id) }}" class="btn-ticket primary">
                                    <i class="fas fa-shopping-cart"></i>
                                    Comprar
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <div class="sidebar">
                @if($event->establishment)
                <div class="establishment-card">
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
                
                <div class="map-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-map"></i>
                            Mapa do local
                        </h3>
                    </div>
                    <div class="map-container">
                        <iframe
                            src="{{ $embedSrc }}"
                            class="map-iframe"
                            allowfullscreen
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
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
.event-container { width: 100%; margin: 0; padding: 0; }

.hero-section { margin: 0 !important; border-radius: 0; overflow: hidden; box-shadow: none; }

.hero-image {
    height: 500px;
    background-size: cover;
    background-position: center;
    position: relative;
}

.hero-overlay {
    background: linear-gradient(135deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.3) 100%);
    height: 100%;
    display: flex;
    align-items: flex-end;
    padding: 3rem 2rem;
}

.hero-content h1 {
    font-size: clamp(2rem, 5vw, 3.5rem);
    font-weight: 800;
    background: linear-gradient(135deg, #fff 0%, #f8fafc 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0 0 1rem 0;
    line-height: 1.1;
}

.event-date {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #fff;
    font-size: 1.1rem;
    font-weight: 500;
}

.event-details { background: #fff; padding: 2rem; border-radius: 0; box-shadow: none; }

.description {
    font-size: 1.1rem;
    line-height: 1.7;
    color: #374151;
    margin-bottom: 2.5rem;
}

.action-buttons { display: flex; flex-wrap: wrap; gap: 0.5rem; justify-content: flex-start; }

.btn-action { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.6rem; border-radius: 999px; font-weight: 600; text-decoration: none; transition: all 0.2s ease; font-size: 0.8rem; position: relative; line-height: 1; }

.btn-action::before {
    content: attr(data-tooltip);
    position: absolute;
    bottom: -40px;
    left: 50%;
    transform: translateX(-50%);
    background: #1f2937;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.8rem;
    white-space: nowrap;
    opacity: 0;
    transition: all 0.3s ease;
    pointer-events: none;
    z-index: 10;
}

.btn-action:hover::before {
    opacity: 1;
    bottom: -35px;
}

.maps-btn { background: #E8F0FE; color: #1a73e8; border: 1px solid #d2e3fc; }
.waze-btn { background: #FFF4E5; color: #ea580c; border: 1px solid #ffe1c7; }
.whatsapp-btn { background: #E7F6EE; color: #059669; border: 1px solid #c7ecd8; }
.share-btn.primary { background: #EEF2FF; color: #4338CA; border: 1px solid #e0e7ff; cursor: pointer; }

.btn-action:hover { transform: translateY(0); filter: brightness(0.95); }

.share-dropdown { position: relative; display: inline-block; }
.share-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    box-shadow: 0 10px 20px rgba(0,0,0,0.08);
    padding: 0.5rem;
    min-width: 180px;
    display: none;
    z-index: 30;
}
.share-menu.show { display: block; }
.share-menu a, .share-menu .copy-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.5rem 0.6rem;
    border-radius: 8px;
    text-decoration: none;
    color: #111827;
    background: transparent;
}
.share-menu a:hover, .share-menu .copy-link:hover { background: #f3f4f6; }
.share-menu .copy-link { border: 0; cursor: pointer; }

.main-content { display: grid; grid-template-columns: 1fr; gap: 0; margin-top: 0; }

@media (max-width: 992px) {
    .main-content { grid-template-columns: 1fr; gap: 0; }
    .sidebar { order: -1; }
}

.tickets-section {
    background: #fff;
    border-radius: 0;
    overflow: hidden;
    box-shadow: none;
}

.section-header {
    padding: 1.5rem 1.75rem;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: 1px solid #e2e8f0;
}

.section-title {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.tickets-grid { display: grid; gap: 1rem; padding: 1.5rem; }

.ticket-card {
    display: grid;
    grid-template-columns: 1fr auto 180px;
    gap: 1rem;
    padding: 1.5rem;
    background: #fafbfc;
    border-radius: 0;
    border-top: 1px solid #e2e8f0;
    transition: all 0.3s ease;
    align-items: center;
}

.ticket-card:hover {
    background: #fff;
    transform: none;
}

.ticket-name {
    margin: 0 0 0.25rem 0;
    font-size: 1.3rem;
    font-weight: 700;
    color: #1e293b;
}

.ticket-type {
    color: #64748b;
    font-size: 0.95rem;
    font-weight: 500;
}

.ticket-price .price {
    font-size: 2rem;
    font-weight: 800;
    color: #059669;
    line-height: 1;
}

.ticket-actions {
    display: flex;
    gap: 0.75rem;
}

.btn-ticket {
    flex: 1;
    padding: 0.875rem 1.25rem;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.btn-ticket.primary {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
}

.btn-ticket.secondary {
    background: #f8fafc;
    color: #475569;
    border: 2px solid #e2e8f0;
}

.btn-ticket:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.2);
}

.sidebar > * {
    margin-bottom: 1.5rem;
}

.establishment-card,
.map-card {
    background: #fff;
    border-radius: 0;
    overflow: hidden;
    box-shadow: none;
}

.card-header {
    padding: 1.25rem 1.75rem;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}

.card-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.establishment-info {
    padding: 2rem;
}

.establishment-name {
    margin: 0 0 1rem 0;
    font-size: 1.4rem;
    font-weight: 700;
    color: #1e293b;
}

.address {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 1rem;
    color: #374151;
    font-size: 1rem;
}

.location-details {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #64748b;
    font-size: 0.95rem;
    margin-bottom: 1.5rem;
}

.neighborhood, .city {
    font-weight: 500;
}

.btn-establishment {
    width: 100%;
    padding: 1rem;
    background: linear-gradient(135deg, #6b7280, #4b5563);
    color: white;
    text-decoration: none;
    border-radius: 12px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.btn-establishment:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 25px -5px rgba(107, 114, 128, 0.4);
}

.map-container {
    height: 300px;
    overflow: hidden;
    border-radius: 0;
}

.map-iframe {
    width: 100%;
    height: 100%;
    border: 0;
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.event-container > * {
    animation: fadeInUp 0.6s ease-out forwards;
}

.event-container > *:nth-child(1) { animation-delay: 0.1s; }
.event-container > *:nth-child(2) { animation-delay: 0.2s; }

/* Responsive */
@media (max-width: 768px) {
    .event-container { max-width: 100%; margin: 0; padding: 0 !important; }

    .container-fluid {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .container-fluid > .row {
        margin-left: 0;
        margin-right: 0;
    }
    
    .hero-overlay { padding: 2rem 1.5rem; }
    .event-details { padding: 1.5rem; }
    
    .action-buttons { flex-direction: row; align-items: center; gap: 0.4rem; }
    
    
    
    .ticket-card {
        grid-template-columns: 1fr;
        gap: 1rem;
        text-align: center;
    }
    
    .ticket-actions {
        justify-content: center;
    }
}

/* Mobile full-bleed (estilo Instagram) */
@media (max-width: 767.98px) {
    .content-grid { padding: 0 !important; }
    .event-details { padding-left: 0 !important; padding-right: 0 !important; border: 0 !important; border-radius: 0 !important; }
    .tickets-section { padding-left: 0 !important; padding-right: 0 !important; border: 0 !important; border-radius: 0 !important; }
}
</style>
@endpush

@push('scripts')
<script>
function shareEvent(title, text, url) {
    if (navigator.share) {
        navigator.share({
            title: title,
            text: text,
            url: url
        }).catch(() => {
            fallbackShare(url, title);
        });
    } else {
        fallbackShare(url, title);
    }
}

function fallbackShare(url, title) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(() => {
            showToast('Link copiado! Cole onde quiser compartilhar.', 'success');
        }).catch(() => {
            window.open('https://wa.me/?text=' + encodeURIComponent(title + ' ' + url), '_blank');
        });
    } else {
        window.open('https://wa.me/?text=' + encodeURIComponent(title + ' ' + url), '_blank');
    }
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10b981' : '#3b82f6'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        z-index: 10000;
        transform: translateX(400px);
        transition: all 0.3s ease;
        font-weight: 500;
    `;
    
    document.body.appendChild(toast);
    
    requestAnimationFrame(() => {
        toast.style.transform = 'translateX(0)';
    });
    
    setTimeout(() => {
        toast.style.transform = 'translateX(400px)';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 3000);
}

// Smooth scroll and animations
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('shareToggle');
    const menu = document.getElementById('shareMenu');
    if (toggle && menu) {
        toggle.addEventListener('click', function(e){
            e.stopPropagation();
            menu.classList.toggle('show');
        });
        document.addEventListener('click', function(){
            menu.classList.remove('show');
        });
        const copyBtn = menu.querySelector('.copy-link');
        if (copyBtn) {
            copyBtn.addEventListener('click', function(e){
                e.preventDefault();
                e.stopPropagation();
                const link = this.getAttribute('data-url');
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(link).then(function(){
                        showToast('Link copiado!', 'success');
                    });
                }
                menu.classList.remove('show');
            });
        }
    }
    // Intersection Observer for animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.ticket-card, .establishment-card, .map-card').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
        observer.observe(el);
    });

    // Button hover effects
    document.querySelectorAll('.btn-action, .btn-ticket').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px) scale(1.02)';
        });
        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
});
</script>

<style>
.toast {
    backdrop-filter: blur(10px);
}
</style>
@endpush
