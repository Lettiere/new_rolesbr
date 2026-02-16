@extends('_layout.site.site_default')
@section('content')
<div class="container py-4">
    @php
        $bar = $product->establishment;
        $img = '';
        if (!empty($product->foto_url)) {
            $img = asset(ltrim($product->foto_url, '/'));
        } elseif ($bar && $bar->imagem) {
            $img = asset(ltrim($bar->imagem, '/'));
        } else {
            $img = 'https://images.unsplash.com/photo-1514933651103-005eec06c04b?w=800&h=600&fit=crop';
        }
    @endphp
    <div class="row g-4">
        <div class="col-md-6">
            <div class="ratio ratio-4x3 bg-light rounded overflow-hidden">
                <img src="{{ $img }}" alt="{{ $product->nome }}" class="w-100 h-100" style="object-fit:cover;">
            </div>
        </div>
        <div class="col-md-6">
            <h1 class="h4 fw-bold mb-2">{{ $product->nome }}</h1>
            @if($product->preco !== null)
                <div class="h5 text-primary mb-3">
                    R$ {{ number_format($product->preco, 2, ',', '.') }}
                </div>
            @endif
            @if($product->descricao)
                <p class="text-muted mb-3">{{ $product->descricao }}</p>
            @endif
            @if($bar)
                <p class="mb-3">
                    <span class="fw-semibold">Estabelecimento:</span>
                    <a href="{{ route('site.establishment.show', [$bar->bares_id, \Illuminate\Support\Str::slug($bar->nome)]) }}">
                        {{ $bar->nome }}
                    </a>
                </p>
            @endif
            @if($product->unidade)
                <p class="mb-3 small text-muted">
                    Unidade: {{ $product->unidade }}
                </p>
            @endif
            <div class="d-flex flex-wrap gap-2">
                @if($bar && $bar->endereco)
                    @php
                        $addr = trim(($bar->endereco ?? '') . ' ' . ($bar->bairro_nome ?? '') . ' ' . ($bar->cidade_nome ?? ''));
                        $mapsLink = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($addr);
                    @endphp
                    <a href="{{ $mapsLink }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
                        Ver no mapa
                    </a>
                @endif
                @if($bar && $bar->telefone)
                    @php
                        $waNumber = preg_replace('/\D+/', '', (string) $bar->telefone);
                        $waLink = 'https://wa.me/55'.$waNumber.'?text='.urlencode('Olá, vi o produto '.$product->nome.' no RolesBr');
                    @endphp
                    <a href="{{ $waLink }}" target="_blank" rel="noopener" class="btn btn-outline-success btn-sm">
                        Falar no WhatsApp
                    </a>
                @endif
            </div>
            <div class="mt-4">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        Compartilhar
                    </button>
                    @php
                        $slug = \Illuminate\Support\Str::slug($product->nome ?: 'produto');
                        $shareUrl = url('produto/'.$product->prod_id.'-'.$slug);
                    @endphp
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="https://wa.me/?text={{ urlencode($product->nome.' - '.$shareUrl) }}" target="_blank" rel="noopener">WhatsApp</a></li>
                        <li><a class="dropdown-item" href="https://t.me/share/url?url={{ urlencode($shareUrl) }}&text={{ urlencode($product->nome) }}" target="_blank" rel="noopener">Telegram</a></li>
                        <li><a class="dropdown-item" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener">Facebook</a></li>
                        <li><a class="dropdown-item" href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ urlencode($product->nome) }}" target="_blank" rel="noopener">Twitter/X</a></li>
                    </ul>
                </div>
                <div class="mt-2 small text-muted">
                    Compartilhe este produto com seus amigos.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
