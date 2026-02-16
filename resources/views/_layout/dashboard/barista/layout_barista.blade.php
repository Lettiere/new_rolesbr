<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Barista') - RolesBr</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            min-height: 100vh;
            background-color: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #212529;
            line-height: 1.5;
        }
        h1, h2, h3, h4, h5, h6 {
            font-weight: 600;
        }
        .sidebar {
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }
        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }
        main {
            padding-top: 20px;
        }
        .card {
            border-radius: 0.75rem;
        }
        .table {
            font-size: 0.9rem;
        }
        .table thead th {
            font-weight: 600;
        }
        .border-bottom.d-flex {
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
        }
        .border-bottom.d-flex h1,
        .border-bottom.d-flex h2,
        .border-bottom.d-flex h3 {
            margin-bottom: 0;
        }
        @media (min-width: 992px) {
            .sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                z-index: 100;
            }
            main {
                margin-left: 16.66667%;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            @include('_layout.dashboard.barista.sections.barista_sidebar')

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
