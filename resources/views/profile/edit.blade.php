@extends('_layout.site.site_default')

@section('content')
<div class="container py-3">
    <div class="row">
        <div class="col-12 mb-3">
            <h1 class="h4 mb-1">Editar Perfil #{{ $profile->perfil_id }}</h1>
            <div class="text-muted">
                {{ $profile->user->name ?? 'Usuário' }} ({{ $profile->user->email ?? '' }})
            </div>
        </div>
        <div class="col-12 col-md-8">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <form method="POST" action="{{ route('profile.update', $profile->perfil_id) }}" enctype="multipart/form-data" class="row g-3">
                @csrf
                @method('PUT')
                <div class="col-md-4">
                    <label class="form-label">CPF</label>
                    <input type="text" name="cpf" value="{{ old('cpf', $profile->cpf) }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">RG</label>
                    <input type="text" name="rg" value="{{ old('rg', $profile->rg) }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Telefone</label>
                    <input type="text" name="telefone" value="{{ old('telefone', $profile->telefone) }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Data de nascimento</label>
                    <input type="date" name="data_nascimento" value="{{ old('data_nascimento', $profile->data_nascimento) }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Gênero</label>
                    <select name="genero_id" class="form-select">
                        <option value="">Selecione...</option>
                        @foreach($genders as $g)
                            <option value="{{ $g->genero_id }}" @selected((int) old('genero_id', $profile->genero_id) === (int) $g->genero_id)>
                                {{ $g->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Estado</label>
                    <select name="estado_id" id="estado_id" class="form-select">
                        <option value="">Selecione...</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Cidade</label>
                    <select name="cidade_id" id="cidade_id" class="form-select" disabled>
                        <option value="">Selecione...</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Bairro</label>
                    <select name="bairro_id" id="bairro_id" class="form-select" disabled>
                        <option value="">Selecione...</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Rua (ID)</label>
                    <input type="number" name="rua_id" value="{{ old('rua_id', $profile->rua_id) }}" class="form-control">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Bairro (texto)</label>
                    <input type="text" name="bairro_nome" value="{{ old('bairro_nome', $profile->bairro_nome) }}" class="form-control">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-secondary w-100" id="btnNovoBairro">
                        Novo bairro
                    </button>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Foto de perfil</label>
                    <input type="file" name="foto_perfil" class="form-control" accept="image/*">
                    @if($profile->foto_perfil)
                        @php
                            $previewRaw = $profile->foto_perfil;
                            if (preg_match('#^https?://#', $previewRaw)) {
                                $previewUrl = $previewRaw;
                            } else {
                                $previewUrl = asset(ltrim($previewRaw, '/'));
                            }
                        @endphp
                        <div class="mt-2">
                            <img src="{{ $previewUrl }}" alt="Foto de perfil" class="img-fluid rounded" style="max-height: 140px; object-fit: cover;">
                        </div>
                    @endif
                </div>
                <div class="col-12">
                    <label class="form-label">Bio</label>
                    <textarea name="bio" rows="3" class="form-control">{{ old('bio', $profile->bio) }}</textarea>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary">Salvar</button>
                    <a href="{{ route('profile') }}" class="btn btn-outline-secondary">Voltar</a>
                </div>
            </form>
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
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var estadoSel = document.getElementById('estado_id');
    var cidadeSel = document.getElementById('cidade_id');
    var bairroSel = document.getElementById('bairro_id');
    var selected = {
        estado: @json((string) old('estado_id', $profile->estado_id)),
        cidade: @json((string) old('cidade_id', $profile->cidade_id)),
        bairro: @json((string) old('bairro_id', $profile->bairro_id))
    };
    function fillSelect(sel, rows, valueKey, labelKey, placeholder, selectedValue) {
        sel.innerHTML = '';
        var opt = document.createElement('option');
        opt.value = '';
        opt.textContent = placeholder;
        sel.appendChild(opt);
        rows.forEach(function (r) {
            var o = document.createElement('option');
            o.value = r[valueKey];
            o.textContent = r[labelKey];
            if (String(r[valueKey]) === String(selectedValue)) {
                o.selected = true;
            }
            sel.appendChild(o);
        });
        sel.disabled = rows.length === 0;
    }
    if (estadoSel && cidadeSel && bairroSel) {
        fetch('{{ route('api.geo.estados') }}')
            .then(function (r) { return r.json(); })
            .then(function (rows) {
                fillSelect(estadoSel, rows, 'id', 'nome', 'Selecione...', selected.estado);
                if (selected.estado) {
                    fetch("{{ url('/api/geo/cidades') }}/" + selected.estado)
                        .then(function (r) { return r.json(); })
                        .then(function (cidades) {
                            fillSelect(cidadeSel, cidades, 'id', 'nome', 'Selecione...', selected.cidade);
                            cidadeSel.disabled = false;
                            if (selected.cidade) {
                                fetch("{{ url('/api/geo/bairros') }}/" + selected.cidade)
                                    .then(function (r) { return r.json(); })
                                    .then(function (bairros) {
                                        fillSelect(bairroSel, bairros, 'id', 'nome', 'Selecione...', selected.bairro);
                                        bairroSel.disabled = false;
                                    });
                            }
                        });
                }
            });
        estadoSel.addEventListener('change', function () {
            var id = estadoSel.value;
            fillSelect(cidadeSel, [], 'id', 'nome', 'Selecione...', '');
            fillSelect(bairroSel, [], 'id', 'nome', 'Selecione...', '');
            if (!id) {
                return;
            }
            fetch("{{ url('/api/geo/cidades') }}/" + id)
                .then(function (r) { return r.json(); })
                .then(function (rows) {
                    fillSelect(cidadeSel, rows, 'id', 'nome', 'Selecione...', '');
                });
        });
        cidadeSel.addEventListener('change', function () {
            var id = cidadeSel.value;
            fillSelect(bairroSel, [], 'id', 'nome', 'Selecione...', '');
            if (!id) {
                return;
            }
            fetch("{{ url('/api/geo/bairros') }}/" + id)
                .then(function (r) { return r.json(); })
                .then(function (rows) {
                    fillSelect(bairroSel, rows, 'id', 'nome', 'Selecione...', '');
                });
        });
    }
    var btnNovoBairro = document.getElementById('btnNovoBairro');
    var modalEl = document.getElementById('modalNovoBairro');
    var cidadeNomeInput = document.getElementById('novoBairroCidadeNome');
    var bairroNomeInput = document.getElementById('novoBairroNome');
    var btnSalvarNovoBairro = document.getElementById('btnSalvarNovoBairro');
    if (btnNovoBairro && modalEl && cidadeSel && cidadeNomeInput && bairroSel && bairroNomeInput && btnSalvarNovoBairro) {
        btnNovoBairro.addEventListener('click', function () {
            var option = cidadeSel.options[cidadeSel.selectedIndex];
            if (!cidadeSel.value || !option) {
                alert('Selecione uma cidade antes de cadastrar o bairro.');
                return;
            }
            cidadeNomeInput.value = option.textContent;
            bairroNomeInput.value = '';
            var modal = new bootstrap.Modal(modalEl);
            modal.show();
        });
        btnSalvarNovoBairro.addEventListener('click', function () {
            var nome = bairroNomeInput.value.trim();
            var cidadeId = cidadeSel.value;
            if (!nome || !cidadeId) {
                alert('Informe o nome do bairro.');
                return;
            }
            var csrf = document.querySelector('meta[name="csrf-token"]');
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
                bairroSel.appendChild(opt);
                bairroSel.value = data.id;
                bairroSel.disabled = false;
                var bairroTexto = document.querySelector('input[name="bairro_nome"]');
                if (bairroTexto && !bairroTexto.value) {
                    bairroTexto.value = data.nome;
                }
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
@endsection
