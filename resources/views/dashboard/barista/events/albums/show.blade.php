@extends('_layout.dashboard.barista.layout_barista')
@section('title','Álbum do Evento')
@section('content')
<div class="d-flex align-items-center justify-content-between pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3 m-0">Álbum: {{ $album->titulo }}</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('dashboard.barista.events.albums') }}" class="btn btn-outline-secondary">Voltar</a>
        <form action="{{ route('dashboard.barista.events.albums.destroy', $album->album_id) }}" method="POST" onsubmit="return confirm('Apagar este álbum e todas as fotos?')">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger">Apagar Álbum</button>
        </form>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-5">
        <div class="card mb-3">
            <div class="card-header">Evento</div>
            <div class="card-body small">
                <dl class="row mb-0">
                    <dt class="col-4">Nome</dt><dd class="col-8">{{ $album->event->nome }}</dd>
                    <dt class="col-4">Data</dt><dd class="col-8">{{ optional($album->event->data_inicio)->format('d/m/Y H:i') }}</dd>
                    <dt class="col-4">Bar</dt><dd class="col-8">{{ $album->event->establishment->nome ?? '-' }}</dd>
                    <dt class="col-4">Local</dt><dd class="col-8">{{ $album->event->local_customizado ?: ($album->event->establishment->endereco ?? '-') }}</dd>
                </dl>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header">Logos do Evento (para sobrepor nas fotos)</div>
            <div class="card-body">
                <form action="{{ route('dashboard.barista.events.albums.logos', $album->album_id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-2 align-items-end">
                        <div class="col-6 col-md-3">
                            <input type="file" name="logo_img_1" class="form-control" accept="image/*">
                            @if($album->event->logo_img_1)
                                <img src="{{ $album->event->logo_img_1 }}" class="img-fluid mt-1 rounded border">
                            @endif
                        </div>
                        <div class="col-6 col-md-3">
                            <input type="file" name="logo_img_2" class="form-control" accept="image/*">
                            @if($album->event->logo_img_2)
                                <img src="{{ $album->event->logo_img_2 }}" class="img-fluid mt-1 rounded border">
                            @endif
                        </div>
                        <div class="col-6 col-md-3">
                            <input type="file" name="logo_img_3" class="form-control" accept="image/*">
                            @if($album->event->logo_img_3)
                                <img src="{{ $album->event->logo_img_3 }}" class="img-fluid mt-1 rounded border">
                            @endif
                        </div>
                        <div class="col-6 col-md-3">
                            <input type="file" name="logo_img_4" class="form-control" accept="image/*">
                            @if($album->event->logo_img_4)
                                <img src="{{ $album->event->logo_img_4 }}" class="img-fluid mt-1 rounded border">
                            @endif
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">Essas logos serão aplicadas na barra inferior de todas as novas fotos enviadas para este álbum.</small>
                    <button class="btn btn-sm btn-outline-primary mt-2">Salvar logos</button>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-header">Enviar Fotos (máx 20)</div>
            <div class="card-body">
                <form action="{{ route('dashboard.barista.events.albums.upload', $album->album_id) }}" method="POST" enctype="multipart/form-data" class="d-grid gap-2">
                    @csrf
                    <input type="file" name="fotos[]" class="form-control" multiple required accept="image/*">
                    <div class="text-muted small">As imagens são ajustadas a 16:9 (paisagem) ou 9:16 (retrato), redimensionadas para até 800x600 e otimizadas para tamanho máximo aproximado de 3MB.</div>
                    <button class="btn btn-primary">Enviar</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-header">Fotos do Álbum ({{ count($album->photos) }})</div>
            <div class="card-body">
                @if($album->photos->isEmpty())
                    <div class="text-muted text-center py-5">Nenhuma foto ainda.</div>
                @else
                <div class="row g-3">
                    @foreach($album->photos as $p)
                        <div class="col-6 col-md-4">
                            <div class="border rounded p-2 h-100 d-flex flex-column">
                                <div class="ratio ratio-16x9 mb-2" style="--bs-aspect-ratio:56.25%;">
                                    <a href="{{ $p->nome_arquivo }}" data-album-photo="1" data-index="{{ $loop->index }}" data-src="{{ $p->nome_arquivo }}" class="d-block w-100 h-100">
                                        <img src="{{ $p->nome_arquivo }}" class="w-100 h-100 object-fit-cover rounded" alt="">
                                    </a>
                                </div>
                                <form action="{{ route('dashboard.barista.events.albuns.fotos.destroy', [$album->album_id, $p->foto_id]) }}" method="POST" onsubmit="return confirm('Remover esta foto?')" class="mt-auto">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger w-100">Remover</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="albumPhotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark">
            <div class="modal-body position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                <button type="button" class="btn btn-dark position-absolute top-50 start-0 translate-middle-y" id="albumPhotoPrev">&lsaquo;</button>
                <button type="button" class="btn btn-dark position-absolute top-50 end-0 translate-middle-y" id="albumPhotoNext">&rsaquo;</button>
                <div class="text-center">
                    <img id="albumPhotoModalImg" src="" class="img-fluid" style="max-height:80vh;">
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var items = Array.prototype.slice.call(document.querySelectorAll('[data-album-photo]'));
    if (!items.length) return;
    var modalEl = document.getElementById('albumPhotoModal');
    if (!modalEl) return;
    var modalImg = document.getElementById('albumPhotoModalImg');
    var prevBtn = document.getElementById('albumPhotoPrev');
    var nextBtn = document.getElementById('albumPhotoNext');
    var modal = new bootstrap.Modal(modalEl);
    var current = 0;

    function show(index) {
        if (index < 0) index = items.length - 1;
        if (index >= items.length) index = 0;
        current = index;
        var src = items[current].getAttribute('data-src') || items[current].getAttribute('href');
        if (src) {
            modalImg.src = src;
            modal.show();
        }
    }

    items.forEach(function (el, idx) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            show(idx);
        });
    });

    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            show(current - 1);
        });
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            show(current + 1);
        });
    }

    modalEl.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowLeft') show(current - 1);
        if (e.key === 'ArrowRight') show(current + 1);
    });
});
</script>
@endpush
@endsection
