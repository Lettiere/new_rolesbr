@extends('_layout.dashboard.barista.layout_barista')

@section('title', 'Editar Estabelecimento')

@section('content')
<div class="pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3 m-0">Editar Estabelecimento</h1>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('dashboard.barista.establishments.update', $establishment->bares_id) }}" method="POST" enctype="multipart/form-data" class="row g-3">
            @csrf
            @method('PUT')

            <div class="col-md-6">
                <label class="form-label">Nome</label>
                <input type="text" name="nome" value="{{ old('nome', $establishment->nome) }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Endereço</label>
                <input type="text" name="endereco" value="{{ old('endereco', $establishment->endereco) }}" class="form-control" required>
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
                <input type="text" name="bairro_nome" value="{{ old('bairro_nome', $establishment->bairro_nome) }}" class="form-control">
            </div>

            <div class="col-md-4">
                <label class="form-label">Povoado</label>
                <select name="povoado_id" id="povoado_id" class="form-select" disabled>
                    <option value="">Selecione...</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Rua</label>
                <input type="text" id="rua_texto" class="form-control" placeholder="Informe a rua no endereço">
                <div class="form-text">A rua deve constar no campo Endereço</div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Telefone</label>
                <input type="text" name="telefone" value="{{ old('telefone', $establishment->telefone) }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Horário Início</label>
                <input type="time" name="horario_inicio" value="{{ old('horario_inicio', $establishment->horario_inicio) }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Horário Final</label>
                <input type="time" name="horario_final" value="{{ old('horario_final', $establishment->horario_final) }}" class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label">Tipo de Estabelecimento *</label>
                <select name="tipo_bar" class="form-select" required>
                    <option value="">Selecione</option>
                    @foreach($types as $t)
                        <option value="{{ $t->tipo_bar_id }}" @selected(old('tipo_bar', $establishment->tipo_bar)==$t->tipo_bar_id)>{{ $t->nome }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Site</label>
                <input type="url" name="site" value="{{ old('site', $establishment->site) }}" class="form-control">
            </div>

            <div class="col-12">
                <label class="form-label">Redes Sociais</label>
                <div id="social-list" class="row g-2"></div>
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-social-btn">Adicionar rede social</button>
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label">Latitude</label>
                <input type="text" name="latitude" value="{{ old('latitude', $establishment->latitude) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Longitude</label>
                <input type="text" name="longitude" value="{{ old('longitude', $establishment->longitude) }}" class="form-control">
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnUseDeviceLocation">
                    Usar localização atual (GPS)
                </button>
            </div>
            <div class="col-md-3">
                <label class="form-label">Bebidas</label>
                <input type="text" name="bebidas" value="{{ old('bebidas', $establishment->bebidas) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Capacidade</label>
                <input type="number" name="capacidade" value="{{ old('capacidade', $establishment->capacidade) }}" class="form-control">
            </div>

            <div class="col-12">
                <label class="form-label">Infraestrutura / Comodidades</label>
                <div class="row">
                    @foreach($facilities as $fac)
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="facilities[]" value="{{ $fac->id }}" id="fac-{{ $fac->id }}" @checked(in_array($fac->id, old('facilities', $selectedFacilities ?? [])))>
                                <label class="form-check-label" for="fac-{{ $fac->id }}">{{ $fac->nome }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="col-12">
                <label class="form-label">Descrição</label>
                <textarea name="descricao" rows="4" class="form-control">{{ old('descricao', $establishment->descricao) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Benefícios</label>
                <textarea name="beneficios" rows="3" class="form-control">{{ old('beneficios', $establishment->beneficios) }}</textarea>
            </div>

            <div class="col-md-6">
                <label class="form-label">Imagem de Perfil</label>
                <input type="file" name="imagem" class="form-control" accept="image/*">
                @if($establishment->imagem)
                    <div class="mt-2">
                        <img src="{{ asset($establishment->imagem) }}" alt="Imagem atual" class="img-thumbnail" style="max-height:120px">
                    </div>
                @endif
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="nome_na_lista" value="1" id="nomeNaLista" @checked(old('nome_na_lista', $establishment->nome_na_lista))>
                    <label class="form-check-label" for="nomeNaLista">
                        Nome na lista
                    </label>
                </div>
            </div>

            <div class="col-12 d-flex gap-2">
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
    const selected = {
        estado: '{{ old('estado_id', $establishment->estado_id) }}',
        cidade: '{{ old('cidade_id', $establishment->cidade_id) }}',
        bairro: '{{ old('bairro_id', $establishment->bairro_id) }}',
        povoado: '{{ old('povoado_id', $establishment->povoado_id) }}'
    };
    function fillSelect(sel, rows, valueKey, labelKey, placeholder, selectedValue) {
        sel.innerHTML = '';
        const opt = document.createElement('option');
        opt.value = '';
        opt.textContent = placeholder;
        sel.appendChild(opt);
        rows.forEach(r => {
            const o = document.createElement('option');
            o.value = r[valueKey];
            o.textContent = r[labelKey];
            if (String(r[valueKey]) === String(selectedValue)) o.selected = true;
            sel.appendChild(o);
        });
        sel.disabled = rows.length === 0;
    }
    fetch('{{ route('api.geo.estados') }}')
        .then(r => r.json())
        .then(rows => {
            fillSelect(estadoSel, rows, 'id', 'nome', 'Selecione...', selected.estado);
            if (selected.estado) {
                fetch(`{{ url('/api/geo/cidades') }}/${selected.estado}`)
                    .then(r=>r.json())
                    .then(cidades => {
                        fillSelect(cidadeSel, cidades, 'id', 'nome', 'Selecione...', selected.cidade);
                        cidadeSel.disabled = false;
                        if (selected.cidade) {
                            fetch(`{{ url('/api/geo/bairros') }}/${selected.cidade}`).then(r=>r.json()).then(b=>{ fillSelect(bairroSel, b, 'id','nome','Selecione...', selected.bairro); bairroSel.disabled=false; });
                            fetch(`{{ url('/api/geo/povoados') }}/${selected.cidade}`).then(r=>r.json()).then(p=>{ fillSelect(povoadoSel, p, 'id','nome','Selecione...', selected.povoado); povoadoSel.disabled=false; });
                        }
                    });
            }
        });
    estadoSel.addEventListener('change', () => {
        const id = estadoSel.value;
        fillSelect(cidadeSel, [], 'id','nome','Selecione...', '');
        fillSelect(bairroSel, [], 'id','nome','Selecione...', '');
        fillSelect(povoadoSel, [], 'id','nome','Selecione...', '');
        if (!id) return;
        fetch(`{{ url('/api/geo/cidades') }}/${id}`).then(r=>r.json()).then(rows=>fillSelect(cidadeSel, rows, 'id','nome','Selecione...', ''));
    });
    cidadeSel.addEventListener('change', () => {
        const id = cidadeSel.value;
        fillSelect(bairroSel, [], 'id','nome','Selecione...', '');
        fillSelect(povoadoSel, [], 'id','nome','Selecione...', '');
        if (!id) return;
        fetch(`{{ url('/api/geo/bairros') }}/${id}`).then(r=>r.json()).then(rows=>fillSelect(bairroSel, rows, 'id','nome','Selecione...', ''));
        fetch(`{{ url('/api/geo/povoados') }}/${id}`).then(r=>r.json()).then(rows=>fillSelect(povoadoSel, rows, 'id','nome','Selecione...', ''));
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
        const initialSocials = @json($initialSocials ?? []);
        if (Array.isArray(initialSocials) && initialSocials.length) {
            initialSocials.forEach(function (item) {
                createSocialRow(item.network || '', item.handle || '');
            });
        } else {
            createSocialRow();
        }
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
