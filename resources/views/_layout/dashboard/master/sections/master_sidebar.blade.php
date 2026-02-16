<nav class="navbar navbar-dark bg-dark d-lg-none px-3">
    <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard.master') }}">
        <i class="fas fa-martini-glass-citrus me-2"></i>RolesBr Master
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebarMaster" aria-controls="mobileSidebarMaster" aria-label="Menu">
        <span class="navbar-toggler-icon"></span>
    </button>
</nav>

<div class="offcanvas offcanvas-start text-bg-dark d-lg-none" tabindex="-1" id="mobileSidebarMaster" aria-labelledby="mobileSidebarMasterLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="mobileSidebarMasterLabel">Menu Master</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        @php
            $user = Auth::user();
            $userName = $user->name ?? 'Master';
            $avatar = "https://ui-avatars.com/api/?name=" . urlencode($userName) . "&size=128&background=random";
        @endphp
        <div class="d-flex align-items-center gap-2 mb-3">
            <img src="{{ $avatar }}" alt="Avatar" class="rounded-circle border" width="36" height="36" style="object-fit:cover;">
            <div class="fw-semibold">{{ $userName }}</div>
        </div>
        <ul class="nav flex-column">
            <li><a class="nav-link py-2" href="{{ route('dashboard.master') }}"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
            <li><a class="nav-link py-2" href="#"><i class="fas fa-users me-2"></i>Usuários</a></li>
            <li><a class="nav-link py-2" href="#"><i class="fas fa-calendar me-2"></i>Eventos</a></li>
            <li><a class="nav-link py-2 text-danger" href="{{ route('logout') }}"><i class="fas fa-right-from-bracket me-2"></i>Sair</a></li>
        </ul>
    </div>
</div>

<nav class="col-lg-2 sidebar d-none d-lg-flex flex-column px-3 py-4 bg-dark text-white border-end vh-100">
    <div class="sidebar-header mb-4">
        <h4 class="m-0"><i class="fas fa-martini-glass-citrus me-2"></i>RolesBr <small class="text-muted" style="font-size: 0.6em;">MASTER</small></h4>
    </div>
    @php
        $user = Auth::user();
        $userName = $user->name ?? 'Master';
        $avatar = "https://ui-avatars.com/api/?name=" . urlencode($userName) . "&size=128&background=random";
    @endphp
    <div class="d-flex align-items-center gap-2 mb-3">
        <img src="{{ $avatar }}" alt="Avatar" class="rounded-circle border" width="40" height="40" style="object-fit:cover;">
        <div>
            <div class="fw-bold">{{ $userName }}</div>
            <div class="small text-muted">Administrador</div>
        </div>
    </div>
    
    <ul class="nav flex-column flex-grow-1">
        <li class="nav-item mb-2">
            <a class="nav-link text-white-50" href="{{ url('/') }}">
                <i class="fas fa-globe me-2"></i>Home do site
            </a>
        </li>
        <li class="nav-item"><a class="nav-link active text-white" href="{{ route('dashboard.master') }}"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link text-white-50" href="#"><i class="fas fa-users me-2"></i>Gerenciar Usuários</a></li>
        <li class="nav-item"><a class="nav-link text-white-50" href="#"><i class="fas fa-calendar-check me-2"></i>Aprovar Eventos</a></li>
        <li class="nav-item"><a class="nav-link text-white-50" href="#"><i class="fas fa-cogs me-2"></i>Configurações do Sistema</a></li>
    </ul>

    <div class="mt-auto">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-danger w-100">
                <i class="fas fa-sign-out-alt me-2"></i>Sair
            </button>
        </form>
    </div>
</nav>
