@extends('_layout.dashboard.barista.layout_barista')

@section('title', 'Estabelecimentos')

@section('content')
<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3 m-0">Estabelecimentos</h1>
    <a href="{{ route('dashboard.barista.establishments.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Novo
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($establishments as $est)
                        <tr>
                            <td>{{ $est->nome }}</td>
                            <td>{{ optional($est->type)->nome ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $est->status === 'ativo' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($est->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('dashboard.barista.establishments.show', $est->bares_id) }}" class="btn btn-sm btn-outline-secondary">
                                    Ver
                                </a>
                                <a href="{{ route('dashboard.barista.establishments.edit', $est->bares_id) }}" class="btn btn-sm btn-outline-primary">
                                    Editar
                                </a>
                                <form action="{{ route('dashboard.barista.establishments.destroy', $est->bares_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Inativar este estabelecimento?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Inativar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center p-4 text-muted">Nenhum estabelecimento cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer text-muted small">
        {{ $establishments->count() }} registro(s)
    </div>
</div>
@endsection
