@extends('_layout.dashboard.master.layout_master')

@section('title', 'Dashboard Master')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Painel Administrativo (Master)</h1>
</div>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Usuários</h5>
                <p class="display-4 mb-0">{{ number_format($kpis['users'] ?? 0, 0, ',', '.') }}</p>
                <small>Total de contas ativas</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Perfis</h5>
                <p class="display-4 mb-0">{{ number_format($kpis['profiles'] ?? 0, 0, ',', '.') }}</p>
                <small>Perfis de usuários</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h5 class="card-title">Estabelecimentos</h5>
                <p class="display-4 mb-0">{{ number_format($kpis['establishments'] ?? 0, 0, ',', '.') }}</p>
                <small>Bares e casas cadastradas</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h5 class="card-title">Eventos</h5>
                <p class="display-4 mb-0">{{ number_format($kpis['events'] ?? 0, 0, ',', '.') }}</p>
                <small>Eventos cadastrados</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted mb-1">Ingressos vendidos</h6>
                <p class="h3 mb-0">{{ number_format($kpis['tickets_sold'] ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted mb-1">Receita total em ingressos</h6>
                <p class="h3 mb-0">
                    {{ 'R$ ' . number_format($eventStats['tickets']['revenue_total'] ?? 0, 2, ',', '.') }}
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted mb-1">Posts</h6>
                <p class="h3 mb-0">{{ number_format($kpis['posts'] ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted mb-1">Stories ativos</h6>
                <p class="h3 mb-0">
                    {{ number_format(($kpis['stories_users'] ?? 0) + ($kpis['stories_bars'] ?? 0), 0, ',', '.') }}
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">Perfis por gênero</div>
            <div class="card-body">
                <canvas id="profilesGenderChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">Eventos por status</div>
            <div class="card-body">
                <canvas id="eventsStatusChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">Engajamento (posts e stories)</div>
            <div class="card-body">
                <canvas id="engagementChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">Top usuários por stories</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Usuário</th>
                                <th class="text-end">Stories</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($engagementStats['top_users_stories'] as $row)
                                <tr>
                                    <td>{{ $row->name }}</td>
                                    <td class="text-end">{{ number_format($row->total ?? 0, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-muted text-center">Nenhum dado disponível.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">Perfis por estado</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Estado</th>
                                <th class="text-end">Perfis</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($profileStats['by_state'] as $row)
                                <tr>
                                    <td>{{ $row->label }}</td>
                                    <td class="text-end">{{ number_format($row->total ?? 0, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-muted text-center">Nenhum dado disponível.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">Top bares por ingressos</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Bar</th>
                                <th class="text-end">Ingressos</th>
                                <th class="text-end">Receita</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($establishmentStats['top_bars_tickets'] as $row)
                                <tr>
                                    <td>{{ $row->nome }}</td>
                                    <td class="text-end">{{ number_format($row->total_tickets ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-end">
                                        {{ 'R$ ' . number_format($row->revenue ?? 0, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-muted text-center">Nenhum dado disponível.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') {
        return;
    }

    var genderLabels = @json($profileStats['by_gender']->pluck('label'));
    var genderData = @json($profileStats['by_gender']->pluck('total'));
    var genderCtx = document.getElementById('profilesGenderChart');
    if (genderCtx && genderLabels.length > 0) {
        new Chart(genderCtx, {
            type: 'bar',
            data: {
                labels: genderLabels,
                datasets: [{
                    label: 'Perfis',
                    data: genderData,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    var statusLabels = @json($eventStats['by_status']->pluck('label'));
    var statusData = @json($eventStats['by_status']->pluck('total'));
    var statusCtx = document.getElementById('eventsStatusChart');
    if (statusCtx && statusLabels.length > 0) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData,
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(255, 205, 86, 0.7)',
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    var engagementLabels = ['Hoje', 'Semana', 'Mês'];
    var postsData = [
        {{ $engagementStats['posts']['today'] ?? 0 }},
        {{ $engagementStats['posts']['week'] ?? 0 }},
        {{ $engagementStats['posts']['month'] ?? 0 }}
    ];
    var storiesUsersData = [
        {{ $engagementStats['stories_users']['today'] ?? 0 }},
        {{ $engagementStats['stories_users']['week'] ?? 0 }},
        {{ $engagementStats['stories_users']['month'] ?? 0 }}
    ];
    var storiesBarsData = [
        {{ $engagementStats['stories_bars']['today'] ?? 0 }},
        {{ $engagementStats['stories_bars']['week'] ?? 0 }},
        {{ $engagementStats['stories_bars']['month'] ?? 0 }}
    ];
    var engagementCtx = document.getElementById('engagementChart');
    if (engagementCtx) {
        new Chart(engagementCtx, {
            type: 'line',
            data: {
                labels: engagementLabels,
                datasets: [
                    {
                        label: 'Posts',
                        data: postsData,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        tension: 0.3
                    },
                    {
                        label: 'Stories usuários',
                        data: storiesUsersData,
                        borderColor: 'rgba(255, 159, 64, 1)',
                        backgroundColor: 'rgba(255, 159, 64, 0.2)',
                        tension: 0.3
                    },
                    {
                        label: 'Stories bares',
                        data: storiesBarsData,
                        borderColor: 'rgba(153, 102, 255, 1)',
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
});
</script>
@endpush
