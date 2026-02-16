@extends('_layout.dashboard.barista.layout_barista')
@section('title','Evento')
@section('content')
<div class="d-flex align-items-center justify-content-between pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3 m-0">{{ $event->nome }}</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('dashboard.barista.events.edit',$event->evento_id) }}" class="btn btn-outline-primary">Editar</a>
        <a href="{{ route('dashboard.barista.events.index') }}" class="btn btn-outline-secondary">Voltar</a>
    </div>
</div>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-6"><strong>Estabelecimento:</strong> {{ $event->establishment->nome ?? '-' }}</div>
                    <div class="col-md-6"><strong>Tipo:</strong> {{ $event->type->nome ?? '-' }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-6"><strong>Início:</strong> {{ $event->data_inicio?->format('d/m/Y H:i') }}</div>
                    <div class="col-md-6"><strong>Fim:</strong> {{ $event->data_fim?->format('d/m/Y H:i') }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4"><strong>Status:</strong> {{ $event->status }}</div>
                    <div class="col-md-4"><strong>Visibilidade:</strong> {{ $event->visibilidade }}</div>
                    <div class="col-md-4"><strong>Idade mínima:</strong> {{ $event->idade_minima ?? '-' }}</div>
                </div>
                <div class="mb-2"><strong>Local:</strong> {{ $event->local_customizado ?? '-' }}</div>
                <div><strong>Descrição:</strong><br>{{ $event->descricao ?? '-' }}</div>
            </div>
            <div class="card-footer d-flex gap-2">
                <form method="POST" action="{{ route('events.going.toggle',$event->evento_id) }}">
                    @csrf
                    <button class="btn btn-outline-success" type="submit">Eu vou</button>
                </form>
                <a href="{{ route('dashboard.barista.events.lots.index',$event->evento_id) }}" class="btn btn-outline-primary">Lotes de Ingressos</a>
            </div>
        </div>
        <div class="card">
            <div class="card-header">Ingressos</div>
            <div class="card-body">
                <div class="text-muted">Gerencie os lotes de ingressos pelo menu do evento.</div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Mídia</div>
            <div class="card-body">
                @if(!empty($event->imagem_capa))
                    <div class="ratio ratio-4x3 border rounded overflow-hidden">
                        <img src="{{ Str::startsWith($event->imagem_capa, ['http://','https://']) ? $event->imagem_capa : '/'.ltrim($event->imagem_capa,'/') }}" class="w-100 h-100" style="object-fit:cover;">
                    </div>
                @else
                    <div class="text-muted">Sem imagem de capa.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
