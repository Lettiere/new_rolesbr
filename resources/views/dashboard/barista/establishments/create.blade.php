@extends('_layout.dashboard.barista.layout_barista')

@section('title', 'Novo Estabelecimento')

@section('content')
<div class="d-flex align-items-center justify-content-between pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3 m-0">Novo Estabelecimento</h1>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('dashboard.barista.establishments.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <h2 class="h5 mb-3">Informações gerais</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" value="{{ old('nome') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tipo de Estabelecimento *</label>
                        <select name="tipo_bar" class="form-select" required>
                            <option value="">Selecione</option>
                            @foreach($types as $t)
                                <option value="{{ $t->tipo_bar_id }}" @selected(old('tipo_bar')==$t->tipo_bar_id)>{{ $t->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Capacidade</label>
                        <input type="number" name="capacidade" value="{{ old('capacidade') }}" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Telefone</label>
                        <input type="text" name="telefone" value="{{ old('telefone') }}" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Horário Início</label>
                        <input type="time" name="horario_inicio" value="{{ old('horario_inicio') }}" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Horário Final</label>
                        <input type="time" name="horario_final" value="{{ old('horario_final') }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Site</label>
                        <input type="url" name="site" value="{{ old('site') }}" class="form-control">
                    </div>
                </div>
            </div>

            <div class="mb-4 border-top pt-3">
                <h2 class="h5 mb-3">Endereço</h2>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Endereço</label>
                        <input type="text" name="endereco" value="{{ old('endereco') }}" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Estado</label>
                        <select name="estado_id" id="estado_id" class="form-select">
                            <option value="">Selecione...</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cidade</label>
                        <select name="cidade_id" id="cidade_id" class="form-select" disabled>
                            <option value="">Selecione...</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Bairro</label>
                        <select name="bairro_id" id="bairro_id" class="form-select" disabled>
                            <option value="">Selecione...</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Bairro (texto)</label>
                        <input type="text" name="bairro_nome" value="{{ old('bairro_nome') }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Povoado</label>
                        <select name="povoado_id" id="povoado_id" class="form-select" disabled>
                            <option value="">Selecione...</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Rua</label>
                        <input type="text" id="rua_texto" class="form-control" placeholder="Informe a rua no endereço">
                        <div class="form-text">A rua será informada no campo Endereço</div>
                    </div>
                </div>
            </div>

            <div class="mb-4 border-top pt-3">
                <h2 class="h5 mb-3">Localização (coordenadas)</h2>
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Latitude</label>
                        <input type="text" name="latitude" value="{{ old('latitude') }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Longitude</label>
                        <input type="text" name="longitude" value="{{ old('longitude') }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="btnUseDeviceLocation">
                            Usar localização atual (GPS)
                        </button>
                    </div>
                </div>
            </div>

            <div class="mb-4 border-top pt-3">
                <h2 class="h5 mb-3">Redes sociais</h2>
                <div class="col-12">
                    <div id="social-list" class="row g-2"></div>
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-social-btn">Adicionar rede social</button>
                    </div>
                </div>
            </div>

            <div class="mb-4 border-top pt-3">
                <h2 class="h5 mb-3">Infraestrutura / comodidades</h2>
                <div class="col-12">
                    <div class="row">
                        @foreach($facilities as $fac)
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="facilities[]" value="{{ $fac->id }}" id="fac-{{ $fac->id }}" @checked(in_array($fac->id, old('facilities', [])))>
                                    <label class="form-check-label" for="fac-{{ $fac->id }}">{{ $fac->nome }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mb-4 border-top pt-3">
                <h2 class="h5 mb-3">Conteúdo</h2>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Descrição</label>
                        <textarea name="descricao" rows="4" class="form-control">{{ old('descricao') }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Benefícios</label>
                        <textarea name="beneficios" rows="3" class="form-control">{{ old('beneficios') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="mb-4 border-top pt-3">
                <h2 class="h5 mb-3">Imagem e opções</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Imagem de Perfil</label>
                        <input type="file" name="imagem" class="form-control" accept="image/*">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Galeria de fotos (até 15)</label>
                        <input type="file" name="galeria[]" class="form-control" accept="image/*" multiple>
                        <div class="form-text">Selecione uma ou mais imagens para a galeria.</div>
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="nome_na_lista" value="1" id="nomeNaLista">
                            <label class="form-check-label" for="nomeNaLista">
                                Nome na lista
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('dashboard.barista.establishments.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                <button class="btn btn-primary" type="submit">Salvar</button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const estadoSel = document.getElementById('estado_id');
    const cidadeSel = document.getElementById('cidade_id');
    const bairroSel = document.getElementById('bairro_id');
    const povoadoSel = document.getElementById('povoado_id');

    function fillSelect(sel, rows, valueKey='id', labelKey='nome', placeholder='Selecione...') {
        sel.innerHTML = '';
        const opt = document.createElement('option');
        opt.value = '';
        opt.textContent = placeholder;
        sel.appendChild(opt);
        rows.forEach(r => {
            const o = document.createElement('option');
            o.value = r[valueKey];
            o.textContent = r[labelKey];
            sel.appendChild(o);
        });
        sel.disabled = rows.length === 0;
    }

    fetch('{{ route('api.geo.estados') }}')
        .then(r => r.json())
        .then(rows => fillSelect(estadoSel, rows));

    estadoSel.addEventListener('change', () => {
        const id = estadoSel.value;
        cidadeSel.disabled = true; bairroSel.disabled = true; povoadoSel.disabled = true;
        fillSelect(cidadeSel, []);
        fillSelect(bairroSel, []);
        fillSelect(povoadoSel, []);
        if (!id) return;
        fetch(`{{ url('/api/geo/cidades') }}/${id}`)
            .then(r => r.json())
            .then(rows => fillSelect(cidadeSel, rows));
    });

    cidadeSel.addEventListener('change', () => {
        const id = cidadeSel.value;
        fillSelect(bairroSel, []);
        fillSelect(povoadoSel, []);
        if (!id) return;
        fetch(`{{ url('/api/geo/bairros') }}/${id}`).then(r=>r.json()).then(rows=>fillSelect(bairroSel, rows));
        fetch(`{{ url('/api/geo/povoados') }}/${id}`).then(r=>r.json()).then(rows=>fillSelect(povoadoSel, rows));
    });

    const socialContainer = document.getElementById('social-list');
    const addSocialBtn = document.getElementById('add-social-btn');
    const socialOptions = [
        { value: 'instagram', label: 'Instagram' },
        { value: 'facebook', label: 'Facebook' },
        { value: 'tiktok', label: 'TikTok' },
        { value: 'whatsapp', label: 'WhatsApp' },
        { value: 'youtube', label: 'YouTube' },
    ];

    function createSocialRow(selectedType = '', value = '') {
        const row = document.createElement('div');
        row.className = 'col-12 d-flex align-items-center gap-2 mb-1 social-row';

        const select = document.createElement('select');
        select.className = 'form-select form-select-sm w-auto';
        select.name = 'socials_type[]';
        const optEmpty = document.createElement('option');
        optEmpty.value = '';
        optEmpty.textContent = 'Rede';
        select.appendChild(optEmpty);
        socialOptions.forEach(o => {
            const opt = document.createElement('option');
            opt.value = o.value;
            opt.textContent = o.label;
            if (o.value === selectedType) opt.selected = true;
            select.appendChild(opt);
        });

        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control form-control-sm';
        input.name = 'socials_value[]';
        input.placeholder = 'Usuário ou URL';
        input.value = value || '';

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-outline-danger btn-sm';
        removeBtn.textContent = 'Remover';
        removeBtn.addEventListener('click', () => {
            row.remove();
        });

        row.appendChild(select);
        row.appendChild(input);
        row.appendChild(removeBtn);
        socialContainer.appendChild(row);
    }

    if (addSocialBtn && socialContainer) {
        createSocialRow();
        addSocialBtn.addEventListener('click', () => {
            createSocialRow();
        });
    }

    const latitudeInput = document.querySelector('input[name="latitude"]');
    const longitudeInput = document.querySelector('input[name="longitude"]');
    const btnUseDeviceLocation = document.getElementById('btnUseDeviceLocation');

    // Este botão usa o GPS/rede do dispositivo para preencher latitude/longitude para integração com navegação em mobile.
    if (btnUseDeviceLocation) {
        btnUseDeviceLocation.addEventListener('click', function () {
            if (!navigator.geolocation) {
                alert('Geolocalização não é suportada neste navegador/dispositivo.');
                return;
            }
            btnUseDeviceLocation.disabled = true;
            const originalText = btnUseDeviceLocation.textContent;
            btnUseDeviceLocation.textContent = 'Obtendo localização...';
            navigator.geolocation.getCurrentPosition(function (pos) {
                if (latitudeInput) latitudeInput.value = pos.coords.latitude.toFixed(6);
                if (longitudeInput) longitudeInput.value = pos.coords.longitude.toFixed(6);
                btnUseDeviceLocation.disabled = false;
                btnUseDeviceLocation.textContent = originalText;
            }, function () {
                alert('Não foi possível obter a localização do dispositivo.');
                btnUseDeviceLocation.disabled = false;
                btnUseDeviceLocation.textContent = originalText;
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
