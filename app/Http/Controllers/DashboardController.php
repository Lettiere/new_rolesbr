<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function barista()
    {
        if (Auth::user()->type_user != 1) {
            return redirect('/');
        }
        return view('dashboard.barista.dash_barista');
    }

    public function rolezeiro()
    {
        if (Auth::user()->type_user != 2) {
            return redirect('/');
        }
        return view('dashboard.rolezeiro.dash_rolezeiro');
    }

    public function master()
    {
        // Check for admin role or specific type_user (e.g., 3)
        // Adjust condition based on actual 'master' definition
        if (Auth::user()->role !== 'admin' && Auth::user()->type_user != 3) {
             return redirect('/');
        }
        $kpis = [
            'users' => DB::table('users')->whereNull('deleted_at')->count(),
            'profiles' => DB::table('perfil_usuarios_tb')->count(),
            'establishments' => DB::table('form_perfil_bares_tb')->whereNull('deleted_at')->count(),
            'events' => DB::table('evt_eventos_tb')->whereNull('deleted_at')->count(),
            'tickets_sold' => DB::table('evt_ingressos_vendidos_tb')->whereNull('deleted_at')->count(),
            'products' => DB::table('prod_produtos_tb')->whereNull('deleted_at')->count(),
            'menus' => DB::table('prod_cardapio_tb')->whereNull('deleted_at')->count(),
            'menu_items' => DB::table('prod_cardapio_itens_tb')->whereNull('deleted_at')->count(),
            'posts' => DB::table('user_posts_tb')->whereNull('deleted_at')->count(),
            'stories_users' => DB::table('user_stories_tb')->whereNull('deleted_at')->count(),
            'stories_bars' => DB::table('bar_stories_tb')->whereNull('deleted_at')->count(),
            'event_interests' => DB::table('evt_interesse_evento_tb')->count(),
        ];

        $profilesByGender = DB::table('perfil_usuarios_tb as p')
            ->leftJoin('base_genero_usuario as g', 'g.genero_id', '=', 'p.genero_id')
            ->selectRaw('COALESCE(g.nome, "Não informado") as label, COUNT(*) as total')
            ->groupBy('label')
            ->orderByDesc('total')
            ->get();

        $profilesByState = DB::table('perfil_usuarios_tb as p')
            ->join('base_estados as e', 'e.id', '=', 'p.estado_id')
            ->selectRaw('e.nome as label, COUNT(*) as total')
            ->groupBy('e.id', 'e.nome')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $profilesAgeBuckets = DB::table('perfil_usuarios_tb')
            ->selectRaw(
                "CASE
                    WHEN data_nascimento IS NULL THEN 'Não informado'
                    WHEN TIMESTAMPDIFF(YEAR, data_nascimento, CURDATE()) < 18 THEN 'Menos de 18'
                    WHEN TIMESTAMPDIFF(YEAR, data_nascimento, CURDATE()) BETWEEN 18 AND 24 THEN '18-24'
                    WHEN TIMESTAMPDIFF(YEAR, data_nascimento, CURDATE()) BETWEEN 25 AND 34 THEN '25-34'
                    WHEN TIMESTAMPDIFF(YEAR, data_nascimento, CURDATE()) BETWEEN 35 AND 44 THEN '35-44'
                    WHEN TIMESTAMPDIFF(YEAR, data_nascimento, CURDATE()) BETWEEN 45 AND 54 THEN '45-54'
                    ELSE '55+'
                END as label,
                COUNT(*) as total"
            )
            ->groupBy('label')
            ->get();

        $profileStats = [
            'by_gender' => $profilesByGender,
            'by_state' => $profilesByState,
            'age_buckets' => $profilesAgeBuckets,
        ];

        $eventsTotal = DB::table('evt_eventos_tb')->whereNull('deleted_at')->count();
        $eventsUpcoming = DB::table('evt_eventos_tb')
            ->whereNull('deleted_at')
            ->where('data_inicio', '>=', DB::raw('CURDATE()'))
            ->count();
        $eventsToday = DB::table('evt_eventos_tb')
            ->whereNull('deleted_at')
            ->whereRaw('DATE(data_inicio) = CURDATE()')
            ->count();

        $eventsByStatus = DB::table('evt_eventos_tb')
            ->selectRaw('status as label, COUNT(*) as total')
            ->whereNull('deleted_at')
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        $ticketsTotal = DB::table('evt_ingressos_vendidos_tb')
            ->whereNull('deleted_at')
            ->count();
        $ticketsRevenueTotal = DB::table('evt_ingressos_vendidos_tb')
            ->whereNull('deleted_at')
            ->sum('valor_pago');
        $ticketsLast7d = DB::table('evt_ingressos_vendidos_tb')
            ->whereNull('deleted_at')
            ->where('data_compra', '>=', DB::raw('DATE_SUB(CURDATE(), INTERVAL 7 DAY)'))
            ->count();
        $ticketsRevenueLast7d = DB::table('evt_ingressos_vendidos_tb')
            ->whereNull('deleted_at')
            ->where('data_compra', '>=', DB::raw('DATE_SUB(CURDATE(), INTERVAL 7 DAY)'))
            ->sum('valor_pago');

        $eventStats = [
            'total' => $eventsTotal,
            'upcoming' => $eventsUpcoming,
            'today' => $eventsToday,
            'by_status' => $eventsByStatus,
            'tickets' => [
                'total' => $ticketsTotal,
                'revenue_total' => $ticketsRevenueTotal,
                'last_7d' => $ticketsLast7d,
                'revenue_last_7d' => $ticketsRevenueLast7d,
            ],
        ];

        $postsTotal = DB::table('user_posts_tb')->whereNull('deleted_at')->count();
        $postsToday = DB::table('user_posts_tb')
            ->whereNull('deleted_at')
            ->whereRaw('DATE(created_at) = CURDATE()')
            ->count();
        $postsWeek = DB::table('user_posts_tb')
            ->whereNull('deleted_at')
            ->whereRaw('YEARWEEK(created_at,1) = YEARWEEK(CURDATE(),1)')
            ->count();
        $postsMonth = DB::table('user_posts_tb')
            ->whereNull('deleted_at')
            ->whereRaw('YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())')
            ->count();

        $storiesUsersTotal = DB::table('user_stories_tb')->whereNull('deleted_at')->count();
        $storiesUsersToday = DB::table('user_stories_tb')
            ->whereNull('deleted_at')
            ->whereRaw('DATE(created_at) = CURDATE()')
            ->count();
        $storiesUsersWeek = DB::table('user_stories_tb')
            ->whereNull('deleted_at')
            ->whereRaw('YEARWEEK(created_at,1) = YEARWEEK(CURDATE(),1)')
            ->count();
        $storiesUsersMonth = DB::table('user_stories_tb')
            ->whereNull('deleted_at')
            ->whereRaw('YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())')
            ->count();

        $storiesBarsTotal = DB::table('bar_stories_tb')->whereNull('deleted_at')->count();
        $storiesBarsToday = DB::table('bar_stories_tb')
            ->whereNull('deleted_at')
            ->whereRaw('DATE(created_at) = CURDATE()')
            ->count();
        $storiesBarsWeek = DB::table('bar_stories_tb')
            ->whereNull('deleted_at')
            ->whereRaw('YEARWEEK(created_at,1) = YEARWEEK(CURDATE(),1)')
            ->count();
        $storiesBarsMonth = DB::table('bar_stories_tb')
            ->whereNull('deleted_at')
            ->whereRaw('YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())')
            ->count();

        $topUsersStories = DB::table('user_stories_tb as s')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->selectRaw('s.user_id, u.name, COUNT(*) as total')
            ->whereNull('s.deleted_at')
            ->groupBy('s.user_id', 'u.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $topBarsStories = DB::table('bar_stories_tb as s')
            ->join('form_perfil_bares_tb as b', 'b.bares_id', '=', 's.bares_id')
            ->selectRaw('s.bares_id, b.nome, COUNT(*) as total')
            ->whereNull('s.deleted_at')
            ->groupBy('s.bares_id', 'b.nome')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $engagementStats = [
            'posts' => [
                'total' => $postsTotal,
                'today' => $postsToday,
                'week' => $postsWeek,
                'month' => $postsMonth,
            ],
            'stories_users' => [
                'total' => $storiesUsersTotal,
                'today' => $storiesUsersToday,
                'week' => $storiesUsersWeek,
                'month' => $storiesUsersMonth,
            ],
            'stories_bars' => [
                'total' => $storiesBarsTotal,
                'today' => $storiesBarsToday,
                'week' => $storiesBarsWeek,
                'month' => $storiesBarsMonth,
            ],
            'top_users_stories' => $topUsersStories,
            'top_bars_stories' => $topBarsStories,
        ];

        $productStats = [
            'families' => DB::table('prod_familia_produtos_tb')->whereNull('deleted_at')->count(),
            'types' => DB::table('prod_tipo_produtos_tb')->whereNull('deleted_at')->count(),
            'bases' => DB::table('prod_base_produto_tb')->whereNull('deleted_at')->count(),
        ];

        $establishmentsByState = DB::table('form_perfil_bares_tb as b')
            ->join('base_estados as e', 'e.id', '=', 'b.estado_id')
            ->selectRaw('e.nome as label, COUNT(*) as total')
            ->whereNull('b.deleted_at')
            ->groupBy('e.id', 'e.nome')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $topBarsTickets = DB::table('evt_ingressos_vendidos_tb as iv')
            ->join('evt_eventos_tb as e', 'e.evento_id', '=', 'iv.evento_id')
            ->join('form_perfil_bares_tb as b', 'b.bares_id', '=', 'e.bares_id')
            ->selectRaw('b.bares_id, b.nome, COUNT(iv.ingresso_id) as total_tickets, SUM(iv.valor_pago) as revenue')
            ->whereNull('iv.deleted_at')
            ->groupBy('b.bares_id', 'b.nome')
            ->orderByDesc('total_tickets')
            ->limit(10)
            ->get();

        $establishmentStats = [
            'by_state' => $establishmentsByState,
            'top_bars_tickets' => $topBarsTickets,
        ];

        return view('dashboard.master.dash_master', [
            'kpis' => $kpis,
            'profileStats' => $profileStats,
            'eventStats' => $eventStats,
            'engagementStats' => $engagementStats,
            'productStats' => $productStats,
            'establishmentStats' => $establishmentStats,
        ]);
    }

    public function admin()
    {
        return $this->master();
    }
}
