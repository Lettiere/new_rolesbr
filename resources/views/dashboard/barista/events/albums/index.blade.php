@extends('_layout.dashboard.barista.layout_barista')
@section('title','Álbuns de Eventos')
@section('content')
<div class="d-flex align-items-center justify-content-between pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3 m-0">Álbuns de Eventos</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('dashboard.barista.events.albums.create') }}" class="btn btn-primary">Cadastrar Álbum</a>
        <a href="{{ route('dashboard.barista.events.index') }}" class="btn btn-outline-secondary">Voltar aos Eventos</a>
    </div>
</div>

<div class="card"><div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Evento</th>
                    <th>Data</th>
                    <th>Estabelecimento</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
            @forelse($events as $e)
                <tr>
                    <td>{{ $e->evento_id }}</td>
                    <td>{{ $e->nome }}</td>
                    <td>{{ $e->data_inicio?->format('d/m/Y H:i') }}</td>
                    <td>{{ $e->establishment->nome ?? '-' }}</td>
                    <td class="text-end">
                        <a href="{{ route('dashboard.barista.events.albums.create', ['evento_id'=>$e->evento_id]) }}" class="btn btn-sm btn-outline-primary">Criar Álbum</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">Nenhum evento encontrado.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $events->links() }}
</div></div>
@endsection
