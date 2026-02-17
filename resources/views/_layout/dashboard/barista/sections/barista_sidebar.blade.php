<nav class="navbar navbar-dark bg-dark d-lg-none px-3">
    <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard.barista') }}">
        <i class="fas fa-martini-glass-citrus me-2"></i>RolesBr
    </a>
    <a class="btn btn-outline-light btn-sm me-2" href="{{ route('dashboard.barista') }}" title="Voltar para a Dash">
        <i class="fas fa-house"></i>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebarBarista" aria-controls="mobileSidebarBarista" aria-label="Menu">
        <span class="navbar-toggler-icon"></span>
    </button>
</nav>

<div class="offcanvas offcanvas-start text-bg-dark d-lg-none" tabindex="-1" id="mobileSidebarBarista" aria-labelledby="mobileSidebarBaristaLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="mobileSidebarBaristaLabel">Menu</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        @php
            $user = Auth::user();
            $userName = $user->name ?? 'Usuário';
            $avatar = $user->foto
                ? $user->foto
                : "https://ui-avatars.com/api/?name=" . urlencode($userName) . "&size=128&background=random";
        @endphp
        <div class="d-flex align-items-center gap-2 mb-3">
            <a href="{{ route('profile') }}" class="text-decoration-none d-flex align-items-center gap-2">
                <img src="{{ $avatar }}" alt="Avatar" class="rounded-circle border" width="36" height="36" style="object-fit:cover;">
                <div class="fw-semibold text-light">{{ $userName }}</div>
            </a>
        </div>
        <ul class="nav flex-column">
            <li><a class="nav-link py-2" href="{{ route('dashboard.barista') }}"><i class="fas fa-house me-2"></i>Home</a></li>
            <li><a class="nav-link py-2" href="{{ route('dashboard.barista.establishments.index') }}"><i class="fas fa-store me-2"></i>Estabelecimentos</a></li>
            <li><a class="nav-link py-2" href="{{ route('profile') }}"><i class="fas fa-user me-2"></i>Meu perfil</a></li>
            <li><a class="nav-link py-2" href="{{ url('dashboard/perfil/stories/ui') }}"><i class="fas fa-circle-play me-2"></i>Criar Stories</a></li>
        </ul>

        <div class="mt-3">
            <div class="small text-secondary mb-2 px-1">Produtos</div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('dashboard.barista.products.index') }}" class="badge rounded-pill bg-primary text-decoration-none"><i class="fas fa-boxes-stacked me-1"></i>Lista</a>
                <a href="{{ route('dashboard.barista.products.families.index') }}" class="badge rounded-pill bg-secondary text-decoration-none">Famílias</a>
                <a href="{{ route('dashboard.barista.products.types.index') }}" class="badge rounded-pill bg-secondary text-decoration-none">Tipos</a>
                <a href="{{ route('dashboard.barista.products.bases.index') }}" class="badge rounded-pill bg-secondary text-decoration-none">Bases</a>
            </div>
        </div>

        <div class="mt-3">
            <div class="small text-secondary mb-2 px-1">Eventos</div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('dashboard.barista.events.index') }}" class="badge rounded-pill bg-primary text-decoration-none"><i class="fas fa-calendar-days me-1"></i>Eventos</a>
                <a href="{{ url('dashboard/barista/eventos/tipos') }}" class="badge rounded-pill bg-secondary text-decoration-none">Tipos</a>
                <a href="{{ url('dashboard/barista/eventos/estilos') }}" class="badge rounded-pill bg-secondary text-decoration-none">Estilos</a>
                <a href="{{ url('dashboard/barista/eventos/albuns') }}" class="badge rounded-pill bg-secondary text-decoration-none">Álbuns</a>
            </div>
        </div>

        <hr class="dropdown-divider bg-light opacity-25 my-3">
        <a class="btn btn-outline-danger w-100" href="{{ route('logout') }}"><i class="fas fa-right-from-bracket me-2"></i>Sair</a>
        <div class="pt-4 text-center small text-secondary">
            <span>{{ date('Y') }} &copy; RolesBr</span>
        </div>
    </div>
</div>

<nav class="col-lg-2 sidebar d-none d-lg-flex flex-column px-3 py-4 bg-light border-end vh-100">
    <div class="sidebar-header mb-4">
        <h4 class="m-0"><i class="fas fa-martini-glass-citrus me-2"></i>RolesBr</h4>
    </div>
    @php
        $user = Auth::user();
        $userName = $user->name ?? 'Usuário';
        $avatar = $user->foto
            ? $user->foto
            : "https://ui-avatars.com/api/?name=" . urlencode($userName) . "&size=128&background=random";
    @endphp
    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="{{ route('profile') }}" class="d-flex align-items-center gap-2 text-decoration-none text-dark">
            <img src="{{ $avatar }}" alt="Avatar" class="rounded-circle border" width="40" height="40" style="object-fit:cover;">
            <div>
                <div class="fw-bold">{{ $userName }}</div>
                <div class="small text-muted">Barista</div>
            </div>
        </a>
    </div>
    
    <ul class="nav flex-column flex-grow-1">
        <li class="nav-item mb-2">
            <a class="nav-link py-2" href="{{ url('/') }}">
                <i class="fas fa-globe me-2"></i>Home do site
            </a>
        </li>
        <li class="nav-item"><a class="nav-link active" href="{{ route('dashboard.barista') }}"><i class="fas fa-house me-2"></i>Home</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('dashboard.barista.establishments.index') }}"><i class="fas fa-store me-2"></i>Estabelecimento</a></li>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="menuProdutos" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-boxes-stacked me-2"></i>Produtos
            </a>
            <ul class="dropdown-menu" aria-labelledby="menuProdutos">
                <li><a class="dropdown-item" href="{{ route('dashboard.barista.products.index') }}">Lista de Produtos</a></li>
                <li><a class="dropdown-item" href="{{ route('dashboard.barista.products.families.index') }}">Famílias</a></li>
                <li><a class="dropdown-item" href="{{ route('dashboard.barista.products.types.index') }}">Tipos</a></li>
                <li><a class="dropdown-item" href="{{ route('dashboard.barista.products.bases.index') }}">Bases</a></li>
            </ul>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="menuEventos" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-calendar-days me-2"></i>Eventos
            </a>
            <ul class="dropdown-menu" aria-labelledby="menuEventos">
                <li><a class="dropdown-item" href="{{ route('dashboard.barista.events.index') }}">Eventos</a></li>
                <li><a class="dropdown-item" href="{{ url('dashboard/barista/eventos/tipos') }}">Tipos de Evento</a></li>
                <li><a class="dropdown-item" href="{{ url('dashboard/barista/eventos/estilos') }}">Estilos de Atração</a></li>
                <li><a class="dropdown-item" href="{{ url('dashboard/barista/eventos/albuns') }}">Álbuns de Fotos</a></li>
            </ul>
        </li>
        <li class="nav-item mt-3"><span class="text-uppercase small text-muted fw-bold">Conta</span></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('profile') }}"><i class="fas fa-user me-2"></i>Meu Perfil</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ url('dashboard/perfil/stories/ui') }}"><i class="fas fa-circle-play me-2"></i>Criar Stories</a></li>
        <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-cog me-2"></i>Configurações</a></li>
    </ul>

    <div class="mt-auto">
        <a href="{{ route('logout') }}" class="btn btn-outline-danger w-100"><i class="fas fa-sign-out-alt me-2"></i>Sair</a>
    </div>
</nav>
