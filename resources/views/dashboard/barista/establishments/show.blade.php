@extends('_layout.dashboard.barista.layout_barista')

@section('title', 'Detalhes do Estabelecimento')

@section('content')
<div class="pt-3 pb-2 mb-3 border-bottom d-flex justify-content-between">
    <h1 class="h3 m-0">{{ $establishment->nome }}</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('dashboard.barista.establishments.edit', $establishment->bares_id) }}" class="btn btn-primary">Editar</a>
        <a href="{{ route('dashboard.barista.establishments.index') }}" class="btn btn-outline-secondary">Voltar</a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-body text-center">
                @if($establishment->imagem)
                    <img src="{{ asset($establishment->imagem) }}" class="img-fluid rounded" alt="{{ $establishment->nome }}">
                @else
                    <div class="text-muted">Sem imagem</div>
                @endif
            </div>
        </div>
        <div class="card">
            <div class="card-header">Status</div>
            <div class="card-body">
                <span class="badge {{ $establishment->status === 'ativo' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($establishment->status) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="small text-muted">Nome</div>
                        <div class="fw-semibold">{{ $establishment->nome }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Tipo</div>
                        <div>{{ optional($establishment->type)->nome ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Endereço</div>
                        <div>{{ $establishment->endereco }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Telefone</div>
                        <div>{{ $establishment->telefone ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Estado</div>
                        <div>{{ $geo['estado']->nome ?? '-' }} @if(!empty($geo['estado']->uf)) ({{ $geo['estado']->uf }}) @endif</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Cidade</div>
                        <div>{{ $geo['cidade']->nome ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Bairro</div>
                        <div>{{ $geo['bairro']->nome ?? $establishment->bairro_nome ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Povoado</div>
                        <div>{{ $geo['povoado']->nome ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Rua</div>
                        <div>
                            @if(!empty($geo['rua']))
                                @if(!empty($geo['prefixo'])) {{ $geo['prefixo']->sigla ?? $geo['prefixo']->nome }} @endif
                                {{ $geo['rua']->nome }}
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">Latitude</div>
                        <div>{{ $establishment->latitude ?? '-' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">Longitude</div>
                        <div>{{ $establishment->longitude ?? '-' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">Horário Início</div>
                        <div>{{ $establishment->horario_inicio ?? '-' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">Horário Final</div>
                        <div>{{ $establishment->horario_final ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Bebidas</div>
                        <div>{{ $establishment->bebidas ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Capacidade</div>
                        <div>{{ $establishment->capacidade ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Site</div>
                        <div>@if($establishment->site)<a href="{{ $establishment->site }}" target="_blank">{{ $establishment->site }}</a>@else - @endif</div>
                    </div>
                    <div class="col-12">
                        <div class="small text-muted">Descrição</div>
                        <div>{{ $establishment->descricao ?? '-' }}</div>
                    </div>
                    <div class="col-12">
                        <div class="small text-muted">Benefícios</div>
                        <div>{{ $establishment->beneficios ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
