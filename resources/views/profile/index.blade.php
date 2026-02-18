@extends('_layout.site.site_default')

@section('content')
<div class="container py-3">
    <div class="row">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="h4 mb-3">Perfis de Usuário</h1>
            <a href="{{ route('profile') }}" class="btn btn-sm btn-outline-secondary">
                Meu perfil
            </a>
        </div>
        <div class="col-12 mb-3">
            <form class="row g-2" method="GET" action="{{ route('profile.index') }}">
                <div class="col-md-3">
                    <input type="text" name="name" value="{{ request('name') }}" class="form-control" placeholder="Nome">
                </div>
                <div class="col-md-3">
                    <input type="text" name="cpf" value="{{ request('cpf') }}" class="form-control" placeholder="CPF">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100" type="submit">Filtrar</button>
                </div>
            </form>
        </div>
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuário</th>
                            <th>CPF</th>
                            <th>Telefone</th>
                            <th>Cidade</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($profiles as $p)
                            <tr>
                                <td>{{ $p->perfil_id }}</td>
                                <td>
                                    {{ $p->user->name ?? 'N/A' }}<br>
                                    <small class="text-muted">{{ $p->user->email ?? '' }}</small>
                                </td>
                                <td>{{ $p->cpf }}</td>
                                <td>{{ $p->telefone }}</td>
                                <td>{{ $p->cidade_id }}</td>
                                <td>{{ $p->estado_id }}</td>
                                <td class="text-end">
                                    <a href="{{ route('profile.show', $p->perfil_id) }}" class="btn btn-sm btn-primary">
                                        Ver
                                    </a>
                                    <a href="{{ route('profile.edit', $p->perfil_id) }}" class="btn btn-sm btn-outline-primary">
                                        Editar
                                    </a>
                                    <form action="{{ route('profile.destroy', $p->perfil_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Inativar este perfil?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Inativar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Nenhum perfil encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>
                {{ $profiles->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
