<nav class="navbar navbar-dark bg-dark d-lg-none px-3">
    <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard.rolezeiro') }}">
        <i class="fas fa-martini-glass-citrus me-2"></i>RolesBr
    </a>
    <a class="btn btn-outline-light btn-sm me-2" href="{{ route('dashboard.rolezeiro') }}" title="Voltar para a Dash">
        <i class="fas fa-house"></i>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebarRolezeiro" aria-controls="mobileSidebarRolezeiro" aria-label="Menu">
        <span class="navbar-toggler-icon"></span>
    </button>
</nav>

<div class="offcanvas offcanvas-start text-bg-dark d-lg-none" tabindex="-1" id="mobileSidebarRolezeiro" aria-labelledby="mobileSidebarRolezeiroLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="mobileSidebarRolezeiroLabel">Menu</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        @php
            $user = Auth::user();
            $userName = $user->name ?? 'Usuário';
            $avatar = "https://ui-avatars.com/api/?name=" . urlencode($userName) . "&size=128&background=random";
        @endphp
        <div class="d-flex align-items-center gap-2 mb-3">
            <img src="{{ $avatar }}" alt="Avatar" class="rounded-circle border" width="36" height="36" style="object-fit:cover;">
            <div class="fw-semibold">{{ $userName }}</div>
        </div>
        <ul class="nav flex-column">
            <li><a class="nav-link py-2" href="{{ route('dashboard.rolezeiro') }}"><i class="fas fa-house me-2"></i>Feed (Home)</a></li>
            <li><a class="nav-link py-2" href="#"><i class="fas fa-calendar-days me-2"></i>Eventos</a></li>
            <li><a class="nav-link py-2" href="#"><i class="fas fa-ticket me-2"></i>Meus Ingressos</a></li>
            
            <li><hr class="dropdown-divider bg-light opacity-25 my-1"></li>
            <li><a class="nav-link py-2" href="#"><i class="fas fa-user-gear me-2"></i>Configurações</a></li>
            
            <li><hr class="dropdown-divider bg-light opacity-25 my-2"></li>
            <li><a class="nav-link py-2 text-danger" href="{{ route('logout') }}"><i class="fas fa-right-from-bracket me-2"></i>Sair</a></li>
        </ul>
    </div>
</div>

<nav class="col-lg-2 sidebar d-none d-lg-flex flex-column px-3 py-4 bg-light border-end vh-100">
    <div class="sidebar-header mb-4">
        <h4 class="m-0"><i class="fas fa-martini-glass-citrus me-2"></i>RolesBr</h4>
    </div>
    @php
        $user = Auth::user();
        $userName = $user->name ?? 'Usuário';
        $avatar = "https://ui-avatars.com/api/?name=" . urlencode($userName) . "&size=128&background=random";
    @endphp
    <div class="d-flex align-items-center gap-2 mb-3">
        <img src="{{ $avatar }}" alt="Avatar" class="rounded-circle border" width="40" height="40" style="object-fit:cover;">
        <div>
            <div class="fw-bold">{{ $userName }}</div>
            <div class="small text-muted">Rolezeiro</div>
        </div>
    </div>
    
    <ul class="nav flex-column flex-grow-1">
        <li class="nav-item mb-2">
            <a class="nav-link" href="{{ url('/') }}">
                <i class="fas fa-globe me-2"></i>Home do site
            </a>
        </li>
        <li class="nav-item"><a class="nav-link active" href="{{ route('dashboard.rolezeiro') }}"><i class="fas fa-house me-2"></i>Feed</a></li>
        <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-ticket me-2"></i>Meus Ingressos</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('tickets.cart.index') }}"><i class="fas fa-cart-shopping me-2"></i>Carrinho</a></li>
        <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-heart me-2"></i>Favoritos</a></li>
        <li class="nav-item mt-3"><span class="text-uppercase small text-muted fw-bold">Conta</span></li>
        <li class="nav-item"><a class="nav-link" href="#"><i class="fas fa-user me-2"></i>Perfil</a></li>
    </ul>

    <div class="mt-auto">
        <a href="{{ route('logout') }}" class="btn btn-outline-danger w-100"><i class="fas fa-sign-out-alt me-2"></i>Sair</a>
    </div>
</nav>
