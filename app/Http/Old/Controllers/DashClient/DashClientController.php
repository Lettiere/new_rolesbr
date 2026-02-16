<?php

namespace App\Controllers\DashClient;

use App\Controllers\BaseController;
use App\Models\Eventos\EvtEventoModel;
use App\Models\Eventos\EvtIngressoVendidoModel;
use App\Models\Eventos\EvtAlbumModel;
use App\Models\Perfil\PerfilBarClientModel;
use App\Models\Lista\NlListaUsuarioModel;
use App\Models\Produtos\CardapioModel;
use App\Models\Produtos\CardapioItemModel;
use App\Models\Produtos\ProdutoModel;

class DashClientController extends BaseController
{
    public function index()
    {
        // Verifica se o usuário está autenticado e tem type_user = 1
        $session = session();
        $typeUser = $session->get('type_user');
        
        if (!$session->get('logged')) {
            return redirect()->to('/auth/login')
                ->with('error', 'Você precisa estar logado para acessar esta página.');
        }
        
        // Se não for type_user = 1, servir dashboard do usuário consumidor sob /dashboard
        if ($typeUser != 1) {
            $userId = (int)($session->get('user_id') ?? 0);
            $perfilRow = (new \App\Models\User\PerfilUsuarioModel())->where('user_id', $userId)->first() ?? [];
            $profile = [
                'user_id' => $userId,
                'nome' => $session->get('user_name') ?? 'Usuário',
                'imagem' => $perfilRow['foto_perfil'] ?? '',
            ];
            $vipModel = new \App\Models\Lista\NlListaUsuarioModel();
            $vip = [
                'pendentes' => (int)$vipModel->where('user_id', $userId)->where('status', 'pendente')->countAllResults(),
                'total' => (int)$vipModel->where('user_id', $userId)->countAllResults(),
                'link' => '/vip/lista',
            ];
            return view('dash_client/feed/index', ['profile' => $profile, 'vip' => $vip]);
        }
        
        $userId = (int)($session->get('user_id') ?? 0);
        $evtModel = new EvtEventoModel();
        $ingModel = new EvtIngressoVendidoModel();
        $barModel = new PerfilBarClientModel();
        $vipModel = new NlListaUsuarioModel();

        $today = date('Y-m-d');
        $sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));

        $bars = $barModel->where('user_id', $userId)->findAll();
        $barIds = array_map(function($b){ return (int)($b['bares_id'] ?? 0); }, $bars);
        $barIds = array_filter($barIds, function($id){ return $id > 0; });

        $events = [];
        $stats = ['eventos_ativos'=>0,'bares_cadastrados'=>0,'eventos_programados'=>0,'ingressos_7d'=>0];
        $videos = [];
        $profile = [];
        $albums = [];
        $cardapios = [];
        $cardapioItens = [];
        $produtos = [];

        if (!empty($barIds)) {
            $events = $evtModel
                ->select('evento_id, nome, tipo_evento_id, data_inicio, data_fim, status, imagem_capa, video_youtube_url, bares_id')
                ->whereIn('bares_id', $barIds)
                ->where('data_inicio >=', $today)
                ->orderBy('data_inicio', 'ASC')
                ->limit(8)
                ->find();

            $stats = [
                'eventos_ativos' => (int)$evtModel->whereIn('bares_id', $barIds)->where('status', 'ativo')->countAllResults(),
                'bares_cadastrados' => count($barIds),
                'eventos_programados' => (int)$evtModel->whereIn('bares_id', $barIds)->where('data_inicio >=', $today)->countAllResults(),
                'ingressos_7d' => 0,
            ];

            $db = \Config\Database::connect();
            $builder = $db->table('evt_ingressos_vendidos_tb iv');
            $builder->select('COUNT(iv.ingresso_id) AS cnt')
                    ->join('evt_eventos_tb e', 'e.evento_id = iv.evento_id', 'inner')
                    ->whereIn('e.bares_id', $barIds)
                    ->where('iv.data_compra >=', $sevenDaysAgo);
            $row = $builder->get()->getRow();
            $stats['ingressos_7d'] = (int)($row->cnt ?? 0);

            $profile = $bars[0] ?? [];

            $videos = $evtModel
                ->select('nome, video_youtube_url')
                ->whereIn('bares_id', $barIds)
                ->where('video_youtube_url IS NOT NULL', null, false)
                ->where('video_youtube_url <>', '')
                ->orderBy('data_inicio', 'DESC')
                ->limit(3)
                ->find();

            $db = \Config\Database::connect();
            $albums = $db->table('evt_albuns_fotos_tb a')
                ->select('a.album_id, a.titulo, a.evento_id, a.created_at')
                ->join('evt_eventos_tb e', 'e.evento_id = a.evento_id', 'inner')
                ->whereIn('e.bares_id', $barIds)
                ->orderBy('a.created_at', 'DESC')
                ->limit(6)
                ->get()->getResultArray();

            $cardapios = (new CardapioModel())
                ->select('cardapio_id, bares_id, nome, descricao, status')
                ->whereIn('bares_id', $barIds)
                ->where('status', 'ativo')
                ->orderBy('nome', 'ASC')
                ->limit(6)
                ->find();

            $cidList = array_map(function($c){ return (int)($c['cardapio_id'] ?? 0); }, $cardapios);
            $cidList = array_filter($cidList, fn($x)=>$x>0);
            if (!empty($cidList)) {
                $ciRows = (new CardapioItemModel())
                    ->select('cardapio_id, prod_id, preco_override')
                    ->whereIn('cardapio_id', $cidList)
                    ->findAll();
                $pIds = array_unique(array_map(function($r){ return (int)($r['prod_id'] ?? 0); }, $ciRows));
                $pIds = array_filter($pIds, fn($x)=>$x>0);
                $pRows = [];
                if (!empty($pIds)) {
                    $pRows = (new ProdutoModel())
                        ->select('prod_id, nome, descricao, preco')
                        ->whereIn('prod_id', $pIds)
                        ->findAll();
                }
                $pMap = [];
                foreach ($pRows as $pr) {
                    $pMap[(int)$pr['prod_id']] = $pr;
                }
                $cardapioItens = [];
                foreach ($ciRows as $r) {
                    $cid = (int)($r['cardapio_id'] ?? 0);
                    $pid = (int)($r['prod_id'] ?? 0);
                    $cardapioItens[$cid][] = [
                        'prod_id' => $pid,
                        'produto_nome' => $pMap[$pid]['nome'] ?? '',
                        'produto_preco' => (float)($pMap[$pid]['preco'] ?? 0),
                        'unidade' => $pMap[$pid]['unidade'] ?? '',
                        'preco_override' => $r['preco_override'] ?? null,
                        'descricao' => $pMap[$pid]['descricao'] ?? '',
                    ];
                }
            }

            $produtos = (new ProdutoModel())
                ->select('prod_id, nome, descricao, preco, bares_id, status')
                ->whereIn('bares_id', $barIds)
                ->where('status', 'ativo')
                ->orderBy('nome', 'ASC')
                ->limit(8)
                ->find();
        }

        if (empty($profile) && $userId) {
            $profile = [
                'user_id' => $userId,
                'nome' => $session->get('user_name') ?? 'Usuário',
                'imagem' => '',
            ];
        }

        $vip = [
            'pendentes' => (int)$vipModel->where('user_id', $userId)->where('status', 'pendente')->countAllResults(),
            'total' => (int)$vipModel->where('user_id', $userId)->countAllResults(),
            'link' => '/vip/lista'
        ];

        return view('dash/dash_client', [
            'stats' => $stats,
            'events' => $events,
            'videos' => $videos,
            'profile' => $profile,
            'vip' => $vip,
            'albums' => $albums,
            'cardapios' => $cardapios,
            'cardapio_itens' => $cardapioItens,
            'produtos' => $produtos,
        ]);
    }
}
