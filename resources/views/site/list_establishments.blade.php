@extends('_layout.site.site_default')
@section('content')
@section('meta_title', 'Estabelecimentos | RolesBr')
@section('meta_description', 'Encontre bares, casas de eventos e outros estabelecimentos parceiros no RolesBr.')
@section('meta_og_type', 'website')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 fw-bold mb-0">Estabelecimentos</h1>
        <form action="{{ route('site.establishments.index') }}" method="GET" class="d-flex align-items-center gap-2">
            <div class="input-group input-group-sm">
                <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Buscar estabelecimentos">
                <button class="btn btn-outline-secondary" type="submit">Buscar</button>
            </div>
            <input type="hidden" name="estado_id" value="{{ request('estado_id') }}">
            <input type="hidden" name="cidade_id" value="{{ request('cidade_id') }}">
            <input type="hidden" name="bairro_id" value="{{ request('bairro_id') }}">
            <input type="hidden" name="tipo_bar_id" value="{{ request('tipo_bar_id') }}">
            <button type="button"
                    class="btn btn-sm btn-outline-secondary"
                    data-bs-toggle="modal"
                    data-bs-target="#establishmentsFilterModal"
                    title="Filtros">
                <i class="fas fa-filter"></i>
            </button>
            <button type="button"
                    class="btn btn-sm btn-outline-secondary"
                    id="btnClearEstFilters"
                    title="Limpar busca e filtros">
                Limpar
            </button>
        </form>
    </div>
    <div id="establishmentsListWrapper">
        @include('site.partials.establishments_list', ['establishments' => $establishments])
    </div>
</div>

<div class="modal fade" id="establishmentsFilterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filtros de estabelecimentos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="establishmentsFilterForm" action="{{ route('site.establishments.index') }}" method="GET">
                    <input type="hidden" name="q" value="{{ request('q') }}">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="filterEstado" class="form-label">Estado</label>
                            <select name="estado_id" id="filterEstado" class="form-select">
                                <option value="">Selecione</option>
                                @foreach(($estados ?? collect()) as $estado)
                                    <option value="{{ $estado->id }}" @selected((int)request('estado_id') === (int)$estado->id)>
                                        {{ $estado->uf }} - {{ $estado->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            @php
                                $cidadesLista = ($cidades ?? collect());
                                $cidadesDisabled = !($cidadesLista instanceof \Illuminate\Support\Collection) || $cidadesLista->isEmpty();
                            @endphp
                            <label for="filterCidade" class="form-label">Cidade</label>
                            <select name="cidade_id" id="filterCidade" class="form-select" @if($cidadesDisabled) disabled @endif>
                                <option value="">Selecione</option>
                                @foreach($cidadesLista as $cidade)
                                    <option value="{{ $cidade->id }}" @selected((int)request('cidade_id') === (int)$cidade->id)>
                                        {{ $cidade->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-8">
                            @php
                                $bairrosLista = ($bairros ?? collect());
                                $bairrosDisabled = !($bairrosLista instanceof \Illuminate\Support\Collection) || $bairrosLista->isEmpty();
                            @endphp
                            <label for="filterBairro" class="form-label">Bairro</label>
                            <select name="bairro_id" id="filterBairro" class="form-select" @if($bairrosDisabled) disabled @endif>
                                <option value="">Selecione</option>
                                @foreach($bairrosLista as $bairro)
                                    <option value="{{ $bairro->id }}" @selected((int)request('bairro_id') === (int)$bairro->id)>
                                        {{ $bairro->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-end">
                            @auth
                            <button type="button" class="btn btn-outline-secondary w-100" id="btnNovoBairroEst">
                                Novo bairro
                            </button>
                            @endauth
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="filterTipoBar" class="form-label">Tipo de estabelecimento</label>
                            <select name="tipo_bar_id" id="filterTipoBar" class="form-select">
                                <option value="">Selecione</option>
                                @foreach(($tipoEstabelecimentos ?? collect()) as $tipo)
                                    <option value="{{ $tipo->tipo_bar_id }}" @selected((int)request('tipo_bar_id') === (int)$tipo->tipo_bar_id)>
                                        {{ $tipo->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="submit" form="establishmentsFilterForm" class="btn btn-primary">Aplicar filtros</button>
            </div>
        </div>
    </div>
</div>

@auth
<div class="modal fade" id="modalNovoBairroEst" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Novo bairro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formNovoBairroEst" class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Cidade selecionada</label>
                        <input type="text" id="novoBairroCidadeNomeEst" class="form-control" disabled>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Nome do bairro</label>
                        <input type="text" name="nome" id="novoBairroNomeEst" class="form-control">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnSalvarNovoBairroEst">Salvar</button>
            </div>
        </div>
    </div>
</div>
@endauth

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('form[action="{{ route('site.establishments.index') }}"]');
    if (form) {
        var input = form.querySelector('input[name="q"]');
        var wrapper = document.getElementById('establishmentsListWrapper');
        if (input && wrapper) {
            var timer = null;
            input.addEventListener('input', function () {
                if (timer) clearTimeout(timer);
                timer = setTimeout(function () {
                    var url = new URL(form.action, window.location.origin);
                    if (input.value) {
                        url.searchParams.set('q', input.value);
                    } else {
                        url.searchParams.delete('q');
                    }
                    ['estado_id','cidade_id','bairro_id','tipo_bar_id'].forEach(function (name) {
                        var field = form.querySelector('[name="' + name + '"]');
                        if (field && field.value) {
                            url.searchParams.set(name, field.value);
                        } else {
                            url.searchParams.delete(name);
                        }
                    });
                    url.searchParams.set('ajax', '1');
                    fetch(url.toString(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }).then(function (r) {
                        return r.text();
                    }).then(function (html) {
                        wrapper.innerHTML = html;
                    }).catch(function () {});
                }, 300);
            });
        }
    }

    var clearBtn = document.getElementById('btnClearEstFilters');
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            window.location.href = "{{ route('site.establishments.index') }}";
        });
    }

    var cidadesCache = {};
    var bairrosCache = {};
    var estado = document.getElementById('filterEstado');
    var cidade = document.getElementById('filterCidade');
    var bairro = document.getElementById('filterBairro');

    if (estado && cidade) {
        estado.addEventListener('change', function () {
            var id = this.value;
            cidade.innerHTML = '<option value="">Selecione</option>';
            bairro.innerHTML = '<option value="">Selecione</option>';
            cidade.disabled = true;
            bairro.disabled = true;
            if (!id) {
                return;
            }
            var cachedCidades = cidadesCache[id];
            if (cachedCidades) {
                cachedCidades.forEach(function (c) {
                    var optCached = document.createElement('option');
                    optCached.value = c.id;
                    optCached.textContent = c.nome;
                    cidade.appendChild(optCached);
                });
                cidade.disabled = false;
                return;
            }
            fetch("{{ route('api.geo.cidades.bares', ['estadoId' => 'ESTADO_ID']) }}".replace('ESTADO_ID', id))
                .then(function (r) { return r.json(); })
                .then(function (rows) {
                    cidadesCache[id] = rows.slice();
                    rows.forEach(function (c) {
                        var opt = document.createElement('option');
                        opt.value = c.id;
                        opt.textContent = c.nome;
                        cidade.appendChild(opt);
                    });
                    cidade.disabled = false;
                })
                .catch(function () {});
        });
    }

    if (cidade && bairro) {
        cidade.addEventListener('change', function () {
            var id = this.value;
            bairro.innerHTML = '<option value="">Selecione</option>';
            bairro.disabled = true;
            if (!id) {
                return;
            }
            var cachedBairros = bairrosCache[id];
            if (cachedBairros) {
                cachedBairros.forEach(function (b) {
                    var optCached = document.createElement('option');
                    optCached.value = b.id;
                    optCached.textContent = b.nome;
                    bairro.appendChild(optCached);
                });
                bairro.disabled = false;
                return;
            }
            fetch("{{ route('api.geo.bairros.bares', ['cidadeId' => 'CIDADE_ID']) }}".replace('CIDADE_ID', id))
                .then(function (r) { return r.json(); })
                .then(function (rows) {
                    bairrosCache[id] = rows.slice();
                    rows.forEach(function (b) {
                        var opt = document.createElement('option');
                        opt.value = b.id;
                        opt.textContent = b.nome;
                        bairro.appendChild(opt);
                    });
                    bairro.disabled = false;
                })
                .catch(function () {});
        });
    }

    var btnNovoBairroEst = document.getElementById('btnNovoBairroEst');
    var modalElEst = document.getElementById('modalNovoBairroEst');
    var cidadeNomeInputEst = document.getElementById('novoBairroCidadeNomeEst');
    var bairroNomeInputEst = document.getElementById('novoBairroNomeEst');
    var btnSalvarNovoBairroEst = document.getElementById('btnSalvarNovoBairroEst');

    if (btnNovoBairroEst && modalElEst && cidade && cidadeNomeInputEst && bairro && bairroNomeInputEst && btnSalvarNovoBairroEst) {
        btnNovoBairroEst.addEventListener('click', function () {
            var cidadeOption = cidade.options[cidade.selectedIndex];
            if (!cidade.value || !cidadeOption) {
                alert('Selecione uma cidade antes de cadastrar o bairro.');
                return;
            }
            cidadeNomeInputEst.value = cidadeOption.textContent;
            bairroNomeInputEst.value = '';
            var modal = new bootstrap.Modal(modalElEst);
            modal.show();
        });

        btnSalvarNovoBairroEst.addEventListener('click', function () {
            var nome = bairroNomeInputEst.value.trim();
            var cidadeId = cidade.value;
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
                bairro.appendChild(opt);
                bairro.value = data.id;
                bairro.disabled = false;
                var modal = bootstrap.Modal.getInstance(modalElEst);
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
