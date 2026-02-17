<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-KM5YW0MDD8"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-KM5YW0MDD8');
    </script>
    <!-- ===================== -->
    <!-- META BÁSICO -->
    <!-- ===================== -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $defaultTitle = 'RolesBr São Paulo | Eventos, Festas, Bares, Raves, Gospel e Cultura';
        $defaultDescription = 'RolesBr São Paulo reúne eventos de todos os estilos: bares, festas, raves, gospel, cultura alternativa e encontros sociais. Entre em listas e ganhe descontos exclusivos.';
        $defaultKeywords = 'roles sp, eventos são paulo, festas sp, bares sp, rave sp, eventos gospel sp, cultura alternativa sp, listas vip eventos, rolê sp';
        $defaultAuthor = 'RolesBr';
        $defaultImage = asset('uploads/logo/Logo.png');
        $currentUrl = url()->current();
    @endphp

    <title>@yield('meta_title', $defaultTitle)</title>

    <meta name="description" content="@yield('meta_description', $defaultDescription)">
    <meta name="keywords" content="@yield('meta_keywords', $defaultKeywords)">
    <meta name="author" content="@yield('meta_author', $defaultAuthor)">
    <meta name="robots" content="@yield('meta_robots', 'index, follow')">
    <meta name="googlebot" content="@yield('meta_googlebot', 'index, follow')">

    <!-- ===================== -->
    <!-- SEO LOCAL -->
    <!-- ===================== -->
    <meta name="geo.region" content="BR-SP">
    <meta name="geo.placename" content="São Paulo">
    <meta name="geo.position" content="-23.550520;-46.633308">
    <meta name="ICBM" content="-23.550520, -46.633308">

    <link rel="canonical" href="@yield('meta_canonical', $currentUrl)">

    <meta property="og:type" content="@yield('meta_og_type', 'website')">
    <meta property="og:title" content="@yield('meta_og_title', $defaultTitle)">
    <meta property="og:description" content="@yield('meta_og_description', $defaultDescription)">
    <meta property="og:url" content="@yield('meta_og_url', $currentUrl)">
    <meta property="og:site_name" content="@yield('meta_og_site_name', 'RolesBr')">
    <meta property="og:image" content="@yield('meta_image', $defaultImage)">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="@yield('meta_og_locale', 'pt_BR')">

    <meta name="twitter:card" content="@yield('meta_twitter_card', 'summary_large_image')">
    <meta name="twitter:title" content="@yield('meta_twitter_title', $defaultTitle)">
    <meta name="twitter:description" content="@yield('meta_twitter_description', $defaultDescription)">
    <meta name="twitter:image" content="@yield('meta_twitter_image', $defaultImage)">

    <!-- ===================== -->
    <!-- PWA -->
    <!-- ===================== -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#ff6b35">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="RolesBr">
    <link rel="apple-touch-icon" href="/uploads/logos/rolerbr.jpeg">
    <link rel="apple-touch-icon" sizes="192x192" href="/uploads/logos/rolerbr.jpeg">
    <link rel="apple-touch-icon" sizes="512x512" href="/uploads/logos/rolerbr.jpeg">

    <!-- ===================== -->
    <!-- PERFORMANCE -->
    <!-- ===================== -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">

    <!-- ===================== -->
    <!-- CSS -->
    <!-- ===================== -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- ===================== -->
    <!-- ESTILO ORIGINAL -->
    <!-- ===================== -->
    <style>
        :root {
            --primary-color: #ff6b35;
            --secondary-color: #2c3e50;
            --accent-color: #f39c12;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f6f7fb;
            /* Ensure padding for fixed navbar */
            padding-top: 70px;
        }

        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        }

        .sidebar {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
        }

        .search-filter {
            transition: all 0.3s ease;
        }

        .search-filter:focus {
            box-shadow: 0 0 0 0.2rem rgba(255,107,53,0.25);
        }

        .event-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .lista-btn {
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            border: none;
        }

        .lista-btn:hover {
            transform: scale(1.05);
        }

        .stories-wrapper::-webkit-scrollbar {
            height: 4px;
        }

        .stories-wrapper::-webkit-scrollbar-thumb {
            background-color: rgba(0,0,0,0.2);
            border-radius: 10px;
        }

        .ad-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: static;
                height: auto;
            }
        }
        @media (max-width: 767.98px) {
            .container-fluid > .row { --bs-gutter-x: 0; }
            main.col-12.col-md-6.pt-3 { padding-left: 0 !important; padding-right: 0 !important; }
            main.col-12.col-md-6.pt-3 .container,
            main.col-12.col-md-6.pt-3 .container-fluid {
                padding-left: 0 !important;
                padding-right: 0 !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            main.col-12.col-md-6.pt-3 .row { --bs-gutter-x: 0; }
            main.col-12.col-md-6.pt-3 [class*="col-"] { padding-left: 0; padding-right: 0; }
        }
        .mobile-bottom-nav{
            position:fixed;
            left:0;
            right:0;
            bottom:0;
            z-index:1075;
            background:rgba(0,0,0,0.96);
            border-top:1px solid rgba(148,163,184,0.5);
            backdrop-filter:blur(18px);
        }
        .mobile-bottom-nav-inner{
            max-width:540px;
            margin:0 auto;
            padding:.35rem .9rem calc(.35rem + env(safe-area-inset-bottom,0));
        }
        .mobile-bottom-nav-menu{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:.25rem;
        }
        .mobile-bottom-nav-link{
            flex:1 1 0;
            display:flex;
            align-items:center;
            justify-content:center;
            height:40px;
            border-radius:999px;
            border:none;
            background:transparent;
            color:#e5e7eb;
            text-decoration:none;
            font-size:1.15rem;
        }
        .mobile-bottom-nav-link.active{
            color:#facc15;
            background:radial-gradient(circle at top,#facc1533 0,#020617 55%);
            box-shadow:0 12px 22px rgba(0,0,0,.55);
        }
        @media (min-width: 768px){
            .mobile-bottom-nav{display:none;}
        }
    </style>

    @yield('css')
    @stack('styles')
</head>
<body>

    <!-- MODAL PUBLICIDADE DE ENTRADA COM SLIDER -->
    <div class="modal fade" id="promoModal" tabindex="-1" aria-labelledby="promoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0">
                <div id="promoCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">

                        <!-- SLIDE 1 -->
                        <div class="carousel-item active">
                            <div class="row g-0">
                                <div class="col-md-5 d-none d-md-block">
                                    <img src="https://images.unsplash.com/photo-1519677100203-a0e668c92439?w=600&h=800&fit=crop"
                                         alt="Mega Role SP Weekend"
                                         class="img-fluid h-100 w-100"
                                         style="object-fit:cover;border-radius: .3rem 0 0 .3rem;">
                                </div>
                                <div class="col-md-7">
                                    <div class="modal-header border-0 pb-0">
                                        <div>
                                            <span class="badge bg-warning text-dark ad-label mb-1">Publicidade</span>
                                            <h5 class="modal-title fw-bold" id="promoModalLabel">Mega Role SP Weekend</h5>
                                            <small class="text-muted">Vila Olímpia • Sábado, 28/12 • 22h</small>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="small mb-2">
                                            Line-up com os principais DJs de São Paulo, open bar selecionado e vantagens exclusivas para quem entrar com nome na lista.
                                        </p>
                                        <ul class="small mb-3">
                                            <li>Desconto especial via RolesBr</li>
                                            <li>Área VIP para primeiros confirmados</li>
                                            <li>Experiência imersiva de luz e som</li>
                                        </ul>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button class="btn lista-btn text-white">
                                                Colocar nome na lista
                                            </button>
                                            <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                                                Ver outros eventos
                                            </button>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0 pt-0 justify-content-between">
                                        <small class="text-muted">Você pode ver estes destaques apenas uma vez por sessão.</small>
                                        <button type="button" class="btn btn-sm btn-link text-muted" data-bs-dismiss="modal">
                                            Pular
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SLIDE 2 -->
                        <div class="carousel-item">
                            <div class="row g-0">
                                <div class="col-md-5 d-none d-md-block">
                                    <img src="https://images.unsplash.com/photo-1551632811-561732d1e306?w=600&h=800&fit=crop"
                                         alt="Sunset no Terraço"
                                         class="img-fluid h-100 w-100"
                                         style="object-fit:cover;border-radius: .3rem 0 0 .3rem;">
                                </div>
                                <div class="col-md-7">
                                    <div class="modal-header border-0 pb-0">
                                        <div>
                                            <span class="badge bg-warning text-dark ad-label mb-1">Publicidade</span>
                                            <h5 class="modal-title fw-bold">Sunset no Terraço</h5>
                                            <small class="text-muted">Jardins • Domingo, 17h</small>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="small mb-2">
                                            Vista única da cidade, música ao vivo e drink exclusivo para quem vier pelo RolesBr.
                                        </p>
                                        <ul class="small mb-3">
                                            <li>Drink de boas-vindas</li>
                                            <li>Área instagramável</li>
                                            <li>Preço especial na pré-venda</li>
                                        </ul>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button class="btn lista-btn text-white">
                                                Ver detalhes
                                            </button>
                                            <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                                                Ver outros eventos
                                            </button>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0 pt-0 justify-content-between">
                                        <small class="text-muted">Você pode ver estes destaques apenas uma vez por sessão.</small>
                                        <button type="button" class="btn btn-sm btn-link text-muted" data-bs-dismiss="modal">
                                            Pular
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- CONTROLES DO CAROUSEL -->
                    <button class="carousel-control-prev" type="button" data-bs-target="#promoCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Anterior</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#promoCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Próximo</span>
                    </button>

                    <!-- INDICADORES -->
                    <div class="carousel-indicators">
                        <button type="button" data-bs-target="#promoCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                        <button type="button" data-bs-target="#promoCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Topbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top bg-dark bg-opacity-90 py-2">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold fs-3 text-warning" href="/">
                <i class="fas fa-cocktail me-2"></i>RolesBr
            </a>
            <div class="d-flex align-items-center gap-2">

                @auth
                    @php
                        $dashboardRoute = match (Auth::user()->type_user) {
                            1 => 'dashboard.barista',
                            2 => 'dashboard.rolezeiro',
                            3 => 'dashboard.master',
                            default => null,
                        };
                    @endphp
                    @if($dashboardRoute)
                        <a href="{{ route($dashboardRoute) }}" class="btn btn-outline-warning btn-sm me-2 d-none d-md-inline-flex align-items-center">
                            <i class="fas fa-gauge-high me-1"></i>
                            <span>Ir para o dashboard</span>
                        </a>
                    @endif
                @endauth

                <!-- Login/Fotos de Usuário & Redes -->
                <div class="btn-group" role="group">
                    
                    <!-- Dropdown de Usuário -->
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-light btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            @auth
                                @if(Auth::user()->foto)
                                    <img src="{{ Auth::user()->foto }}" alt="Perfil" class="rounded-circle" style="width: 24px; height: 24px; object-fit: cover;">
                                @else
                                    <i class="fas fa-user"></i>
                                @endif
                            @else
                                <i class="fas fa-user"></i>
                            @endauth
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end bg-dark border-secondary">
                            @auth
                                <li><h6 class="dropdown-header text-muted">Olá, {{ Auth::user()->name }}</h6></li>
                                @if($dashboardRoute)
                                    <li><a class="dropdown-item text-light" href="{{ route($dashboardRoute) }}">Ir para o dashboard</a></li>
                                @endif
                                <li><a class="dropdown-item text-light" href="#" id="btnShareSite">Compartilhar o RolesBr</a></li>
                                <li><hr class="dropdown-divider bg-secondary"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-light">Sair</button>
                                    </form>
                                </li>
                            @else
                                <li><a class="dropdown-item text-light" href="{{ route('login') }}">Entrar</a></li>
                                <li><a class="dropdown-item text-light" href="{{ route('register') }}">Cadastrar</a></li>
                                <li><hr class="dropdown-divider bg-secondary"></li>
                                <li><a class="dropdown-item text-light" href="#" id="btnShareSite">Compartilhar o RolesBr</a></li>
                            @endauth
                        </ul>
                    </div>

                    <a href="https://wa.me/5511982301985" class="btn btn-outline-light btn-sm ms-1" target="_blank" rel="noopener" title="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <a href="https://instagram.com/rolesbrsp" class="btn btn-outline-light btn-sm" target="_blank" rel="noopener" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- MAIN LAYOUT 3 COLUMNS -->
    <div class="container-fluid">
        <div class="row">
            
            <!-- LEFT COLUMN (25%) - Hidden on Mobile -->
            <aside class="col-md-3 d-none d-md-block pt-3">
                @section('sidebar_left')
                    @auth
                        @if(Auth::user()->type_user == 2) <!-- Rolezeiro -->
                            @include('includes.sidebars.left_rolezeiro')
                        @else
                            <!-- Espaço em branco reservado para futuras propagandas -->
                        @endif
                    @else
                        <div class="card shadow-sm border-0 mb-3">
                            <div class="card-body text-center">
                                <span class="badge bg-warning text-dark mb-2">Publicidade</span>
                                <p class="card-text small text-muted mb-2">
                                    Anuncie seu evento ou bar aqui e alcance quem ama um rolê em São Paulo.
                                </p>
                                <a href="#" class="btn btn-outline-primary btn-sm w-100">Quero anunciar</a>
                            </div>
                        </div>
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-2">
                                <div class="ratio ratio-16x9 bg-light d-flex align-items-center justify-content-center">
                                    <span class="text-muted small">Espaço para banner 300x250</span>
                                </div>
                            </div>
                        </div>
                    @endauth
                @show
            </aside>

            <!-- MIDDLE COLUMN (50% Desktop / 100% Mobile) -->
            <main class="col-12 col-md-6 pt-3">
                @yield('content')
            </main>

            <!-- RIGHT COLUMN (25%) - Hidden on Mobile -->
            <aside class="col-md-3 d-none d-md-block pt-3">
                @section('sidebar_right')
                    @auth
                        @if(Auth::user()->type_user == 2) <!-- Rolezeiro -->
                            @include('includes.sidebars.right_rolezeiro')
                        @else
                             <!-- Placeholder para outras roles -->
                        @endif
                    @else
                        <!-- Visitante (pode mostrar os mesmos destaques do rolezeiro ou genéricos) -->
                        @include('includes.sidebars.right_rolezeiro') 
                    @endauth
                @show
            </aside>

        </div>
    </div>

    <!-- PWA INSTALL BAR -->
    <div id="pwaInstallBar" class="position-fixed bottom-0 start-0 end-0 d-none" style="z-index:1080;">
        <div class="container">
            <div class="alert alert-dark bg-dark text-white border-0 mb-3 shadow-lg d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning text-dark">Atalho</span>
                    <div>
                        <div class="fw-semibold">Adicionar RolesBr à tela inicial</div>
                        <div class="small text-white-50">Toque em “Instalar” para ter acesso rápido ao app.</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button id="pwaInstallClose" type="button" class="btn btn-sm btn-outline-light">Agora não</button>
                    <button id="pwaInstallBtn" type="button" class="btn btn-sm btn-warning text-dark">Instalar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MOBILE BOTTOM NAV -->
    <nav class="mobile-bottom-nav d-md-none">
        <div class="mobile-bottom-nav-inner">
            <div class="mobile-bottom-nav-menu">
                <a href="{{ url('/') }}" class="mobile-bottom-nav-link {{ request()->is('/') ? 'active' : '' }}" aria-label="Início">
                    <i class="fas fa-house"></i>
                </a>
                <a href="{{ route('site.events.index') }}" class="mobile-bottom-nav-link {{ request()->is('eventos*') ? 'active' : '' }}" aria-label="Eventos">
                    <i class="fas fa-calendar-days"></i>
                </a>
                <a href="{{ route('site.stories.index') }}" class="mobile-bottom-nav-link {{ request()->is('stories*') ? 'active' : '' }}" aria-label="Stories">
                    <i class="fas fa-circle-play"></i>
                </a>
                <a href="{{ route('site.products.index') }}" class="mobile-bottom-nav-link {{ request()->is('produtos*') ? 'active' : '' }}" aria-label="Produtos">
                    <i class="fas fa-martini-glass-citrus"></i>
                </a>
                <a href="{{ route('site.tickets.index') }}" class="mobile-bottom-nav-link {{ request()->is('ingressos*') ? 'active' : '' }}" aria-label="Ingressos">
                    <i class="fas fa-ticket-alt"></i>
                </a>
            </div>
        </div>
    </nav>

    <!-- FOOTER -->
    @includeIf('sections.home.footer')


    <!-- ===================== -->
    <!-- SCRIPTS -->
    <!-- ===================== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/pwa-install.js"></script> <!-- PWA Installer -->

    @yield('js')
    @stack('scripts')

</body>
