<div class="card border-0 shadow-sm mb-3">
    <div class="card-body text-center">
        <div class="mb-3 position-relative d-inline-block">
            <img src="{{ Auth::user()->foto ?? asset('assets/img/user-default.jpg') }}" 
                 alt="{{ Auth::user()->name ?? 'Visitante' }}" 
                 class="rounded-circle border border-3 border-warning"
                 style="width: 80px; height: 80px; object-fit: cover;">
            <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle p-1"></span>
        </div>
        <h5 class="card-title mb-0 fw-bold">{{ Auth::user()->name ?? 'Visitante' }}</h5>
        <p class="text-muted small mb-3">{{ '@' . (Auth::user()->username ?? 'usuario') }}</p>
        
        <div class="d-flex justify-content-around text-center mb-3">
            <div>
                <h6 class="fw-bold mb-0">0</h6>
                <small class="text-muted">Rolês</small>
            </div>
            <div>
                <h6 class="fw-bold mb-0">0</h6>
                <small class="text-muted">Seguindo</small>
            </div>
            <div>
                <h6 class="fw-bold mb-0">0</h6>
                <small class="text-muted">Seguidores</small>
            </div>
        </div>

        <a href="{{ route('profile') ?? '#' }}" class="btn btn-outline-warning btn-sm w-100 rounded-pill">Ver Perfil</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="list-group list-group-flush">
        <a href="/" class="list-group-item list-group-item-action d-flex align-items-center border-0 px-3 py-2 {{ Request::is('/') ? 'active fw-bold' : '' }}">
            <i class="fas fa-home me-3 text-warning"></i> Feed
        </a>
        <a href="#" class="list-group-item list-group-item-action d-flex align-items-center border-0 px-3 py-2">
            <i class="fas fa-compass me-3 text-info"></i> Explorar
        </a>
        <a href="#" class="list-group-item list-group-item-action d-flex align-items-center border-0 px-3 py-2">
            <i class="fas fa-ticket-alt me-3 text-success"></i> Minhas Listas
        </a>
        <a href="#" class="list-group-item list-group-item-action d-flex align-items-center border-0 px-3 py-2">
            <i class="fas fa-heart me-3 text-danger"></i> Favoritos
        </a>
        <a href="#" class="list-group-item list-group-item-action d-flex align-items-center border-0 px-3 py-2">
            <i class="fas fa-bell me-3 text-primary"></i> Notificações
        </a>
        <hr class="my-1">
        <a href="#" class="list-group-item list-group-item-action d-flex align-items-center border-0 px-3 py-2">
            <i class="fas fa-cog me-3 text-secondary"></i> Configurações
        </a>
    </div>
</div>