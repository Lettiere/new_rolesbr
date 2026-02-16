@extends('_layout.dashboard.barista.layout_barista')
@section('title','Editar Evento')
@section('content')
<div class="pt-3 pb-2 mb-3 border-bottom d-flex align-items-center justify-content-between">
    <h1 class="h3 m-0">Editar Evento</h1>
    <a href="{{ route('dashboard.barista.events.index') }}" class="btn btn-outline-secondary">Voltar</a>
</div>
<div class="card">
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('dashboard.barista.events.update', $event->evento_id) }}" method="POST" class="row g-3" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="col-md-6">
                <label class="form-label">Estabelecimento</label>
                <select name="bares_id" class="form-select" required>
                    <option value="">Selecione...</option>
                    @foreach($establishments as $e)
                        <option value="{{ $e->bares_id }}" @selected(old('bares_id',$event->bares_id)==$e->bares_id)>{{ $e->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Tipo de Evento</label>
                <div class="input-group">
                    <select name="tipo_evento_id" id="tipo_evento_id" class="form-select">
                        <option value="">Não definido</option>
                        @foreach($types as $t)
                            <option value="{{ $t->tipo_evento_id }}" @selected(old('tipo_evento_id',$event->tipo_evento_id)==$t->tipo_evento_id)>{{ $t->nome }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalNovoTipoEvento">
                        Novo tipo
                    </button>
                </div>
            </div>
            <div class="col-md-8">
                <label class="form-label">Nome</label>
                <input type="text" name="nome" value="{{ old('nome',$event->nome) }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Idade mínima</label>
                <input type="number" name="idade_minima" value="{{ old('idade_minima',$event->idade_minima) }}" class="form-control" min="0">
            </div>
            <div class="col-md-4">
                <label class="form-label">Data de Início</label>
                <input type="datetime-local" name="data_inicio" value="{{ old('data_inicio', optional($event->data_inicio)->format('Y-m-d\\TH:i')) }}" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Data de Fim</label>
                <input type="datetime-local" name="data_fim" value="{{ old('data_fim', optional($event->data_fim)->format('Y-m-d\\TH:i')) }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Abertura das Portas</label>
                <input type="time" name="hora_abertura_portas" value="{{ old('hora_abertura_portas', optional($event->hora_abertura_portas)->format('H:i')) }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @foreach(['rascunho','publicado','encerrado','cancelado'] as $s)
                        <option value="{{ $s }}" @selected(old('status',$event->status)==$s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Visibilidade</label>
                <select name="visibilidade" class="form-select">
                    @foreach(['publico','privado','nao_listado'] as $v)
                        <option value="{{ $v }}" @selected(old('visibilidade',$event->visibilidade)==$v)>{{ ucfirst(str_replace('_',' ',$v)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Local customizado</label>
                <input type="text" name="local_customizado" value="{{ old('local_customizado',$event->local_customizado) }}" class="form-control" placeholder="Se diferente do endereço do bar">
            </div>
            <div class="col-md-8">
                <label class="form-label">Endereço do evento</label>
                <input type="text" name="endereco_evento" value="{{ old('endereco_evento',$event->endereco_evento) }}" class="form-control" placeholder="Rua, número, bairro, cidade">
            </div>
            <div class="col-md-2">
                <label class="form-label">Latitude</label>
                <input type="text" name="latitude_evento" value="{{ old('latitude_evento',$event->latitude_evento) }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Longitude</label>
                <input type="text" name="longitude_evento" value="{{ old('longitude_evento',$event->longitude_evento) }}" class="form-control">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnEventUseDeviceLocation">
                    Usar localização atual (GPS)
                </button>
            </div>
            <div class="col-12">
                <label class="form-label">Mapa do evento</label>
                <div id="event-map" style="width: 100%; height: 300px;"></div>
            </div>
            <div class="col-12">
                <label class="form-label">Descrição</label>
                <textarea name="descricao" rows="3" class="form-control">{{ old('descricao',$event->descricao) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Imagem de capa do evento</label>
                <input type="file" name="imagem_capa" class="form-control" accept="image/*">
                <small class="text-muted">A imagem será ajustada automaticamente para 16:9 ou 9:16.</small>
                @if($event->imagem_capa)
                    <div class="mt-2">
                        <img src="{{ $event->imagem_capa }}" class="img-fluid rounded border">
                    </div>
                @endif
            </div>
            <div class="col-12">
                <label class="form-label">Vídeo (YouTube URL)</label>
                <input type="url" name="video_youtube_url" value="{{ old('video_youtube_url',$event->video_youtube_url) }}" class="form-control" placeholder="https://youtube.com/...">
            </div>
            <div class="col-12">
                <label class="form-label">Logos do Evento (1 a 4)</label>
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <input type="file" name="logo_img_1" class="form-control" accept="image/*">
                        @if($event->logo_img_1)<img src="{{ $event->logo_img_1 }}" class="img-fluid mt-1 rounded border">@endif
                    </div>
                    <div class="col-md-3">
                        <input type="file" name="logo_img_2" class="form-control" accept="image/*">
                        @if($event->logo_img_2)<img src="{{ $event->logo_img_2 }}" class="img-fluid mt-1 rounded border">@endif
                    </div>
                    <div class="col-md-3">
                        <input type="file" name="logo_img_3" class="form-control" accept="image/*">
                        @if($event->logo_img_3)<img src="{{ $event->logo_img_3 }}" class="img-fluid mt-1 rounded border">@endif
                    </div>
                    <div class="col-md-3">
                        <input type="file" name="logo_img_4" class="form-control" accept="image/*">
                        @if($event->logo_img_4)<img src="{{ $event->logo_img_4 }}" class="img-fluid mt-1 rounded border">@endif
                    </div>
                </div>
                <small class="text-muted">Essas logos serão sobrepostas às fotos na barra inferior.</small>
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-primary">Salvar</button>
            </div>
        </form>
    </div>
</div>
<div class="modal fade" id="modalNovoTipoEvento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Novo Tipo de Evento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formNovoTipoEvento" class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Categoria</label>
                        <input type="text" name="categoria" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Descrição</label>
                        <textarea name="descricao" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-12 form-check">
                        <input class="form-check-input" type="checkbox" name="ativo" value="1" id="novoTipoAtivo" checked>
                        <label class="form-check-label" for="novoTipoAtivo">Ativo</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarNovoTipoEvento">Salvar</button>
            </div>
        </div>
    </div>
</div>
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
@endpush
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var formNovoTipo = document.getElementById('formNovoTipoEvento');
    var btnSalvarNovoTipo = document.getElementById('btnSalvarNovoTipoEvento');
    var tipoSelect = document.getElementById('tipo_evento_id');
    if (btnSalvarNovoTipo && formNovoTipo && tipoSelect) {
        btnSalvarNovoTipo.addEventListener('click', function () {
            var formData = new FormData(formNovoTipo);
            fetch('{{ route('dashboard.barista.events.types.store.ajax') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            }).then(function (r) {
                if (!r.ok) {
                    throw new Error('Erro ao salvar tipo');
                }
                return r.json();
            }).then(function (data) {
                var opt = document.createElement('option');
                opt.value = data.tipo_evento_id;
                opt.textContent = data.nome;
                tipoSelect.appendChild(opt);
                tipoSelect.value = data.tipo_evento_id;
                var modalEl = document.getElementById('modalNovoTipoEvento');
                var modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
                formNovoTipo.reset();
                document.getElementById('novoTipoAtivo').checked = true;
            }).catch(function () {
                alert('Não foi possível criar o tipo de evento.');
            });
        });
    }
    var latInput = document.querySelector('input[name="latitude_evento"]');
    var lngInput = document.querySelector('input[name="longitude_evento"]');
    var btnGps = document.getElementById('btnEventUseDeviceLocation');
    var mapDiv = document.getElementById('event-map');
    var map = null;
    var marker = null;
    function initMap() {
        if (!mapDiv || typeof L === 'undefined') return;
        var lat = parseFloat(latInput && latInput.value ? latInput.value : '-23.55052');
        var lng = parseFloat(lngInput && lngInput.value ? lngInput.value : '-46.63331');
        if (isNaN(lat) || isNaN(lng)) {
            lat = -23.55052;
            lng = -46.63331;
        }
        map = L.map('event-map').setView([lat, lng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(map);
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        marker.on('dragend', function (e) {
            var c = e.target.getLatLng();
            if (latInput) latInput.value = c.lat.toFixed(6);
            if (lngInput) lngInput.value = c.lng.toFixed(6);
        });
        map.on('click', function (e) {
            var c = e.latlng;
            marker.setLatLng(c);
            if (latInput) latInput.value = c.lat.toFixed(6);
            if (lngInput) lngInput.value = c.lng.toFixed(6);
        });
    }
    initMap();
    if (btnGps && latInput && lngInput) {
        btnGps.addEventListener('click', function () {
            if (!navigator.geolocation) {
                alert('Geolocalização não é suportada neste dispositivo.');
                return;
            }
            btnGps.disabled = true;
            var original = btnGps.textContent;
            btnGps.textContent = 'Obtendo localização...';
            navigator.geolocation.getCurrentPosition(function (pos) {
                var lat = pos.coords.latitude;
                var lng = pos.coords.longitude;
                latInput.value = lat.toFixed(6);
                lngInput.value = lng.toFixed(6);
                if (map && marker) {
                    marker.setLatLng([lat, lng]);
                    map.setView([lat, lng], 15);
                }
                btnGps.disabled = false;
                btnGps.textContent = original;
            }, function () {
                alert('Não foi possível obter a localização do dispositivo.');
                btnGps.disabled = false;
                btnGps.textContent = original;
            }, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            });
        });
    }
});
</script>
@endpush
@endsection
