<?php

namespace App\Controllers\DashUser;

use App\Controllers\BaseController;
use App\Models\Lista\NlListaUsuarioModel;
use App\Models\User\PerfilUsuarioModel;

class DashUserController extends BaseController
{
    public function index()
    {
        // Verifica se o usuário está autenticado e tem type_user = 2
        $session = session();
        $typeUser = $session->get('type_user');
        
        if (!$session->get('logged')) {
            return redirect()->to('/auth/login')
                ->with('error', 'Você precisa estar logado para acessar esta página.');
        }

        // Se não for type_user = 2, redireciona para o dashboard correto
        if ($typeUser != 2) {
            return redirect()->to('/dashboard')
                ->with('error', 'Acesso não autorizado para este tipo de usuário.');
        }
        
        $userId = (int) ($session->get('user_id') ?? 0);
        $perfilRow = (new PerfilUsuarioModel())->where('user_id', $userId)->first() ?? [];
        $profile = [
            'user_id' => $userId,
            'nome' => $session->get('user_name') ?? 'Usuário',
            'imagem' => $perfilRow['foto_perfil'] ?? '',
        ];
        $vipModel = new NlListaUsuarioModel();
        $vip = [
            'pendentes' => (int)$vipModel->where('user_id', $userId)->where('status', 'pendente')->countAllResults(),
            'total' => (int)$vipModel->where('user_id', $userId)->countAllResults(),
            'link' => '/vip/lista',
        ];
        return view('dash_client/feed/index', ['profile' => $profile, 'vip' => $vip]);
    }

    public function eventos()
    {
        $session = session();
        if (!$session->get('logged')) {
            return redirect()->to('/auth/login')->with('error', 'Login necessário.');
        }
        $userId = (int)($session->get('user_id') ?? 0);
        $perfilRow = (new \App\Models\User\PerfilUsuarioModel())->where('user_id', $userId)->first() ?? [];
        $profile = [
            'user_id' => $userId,
            'nome' => $session->get('user_name') ?? 'Usuário',
            'imagem' => $perfilRow['foto_perfil'] ?? '',
        ];
        return view('dash_user/eventos/index', ['profile' => $profile]);
    }

    public function estabelecimentos()
    {
        $session = session();
        if (!$session->get('logged')) {
            return redirect()->to('/auth/login')->with('error', 'Login necessário.');
        }
        $userId = (int)($session->get('user_id') ?? 0);
        $perfilRow = (new \App\Models\User\PerfilUsuarioModel())->where('user_id', $userId)->first() ?? [];
        $profile = [
            'user_id' => $userId,
            'nome' => $session->get('user_name') ?? 'Usuário',
            'imagem' => $perfilRow['foto_perfil'] ?? '',
        ];
        return view('dash_user/estabelecimentos/index', ['profile' => $profile]);
    }

    public function ingressos()
    {
        $session = session();
        if (!$session->get('logged') || $session->get('type_user') != 2) {
            return redirect()->to('/dashboard')->with('error', 'Acesso não autorizado.');
        }
        $statusFilter = strtolower(trim($this->request->getGet('status') ?? ''));
        $page = (int)($this->request->getGet('page') ?? 1);
        if ($page < 1) { $page = 1; }
        $perPage = (int)($this->request->getGet('per_page') ?? 12);
        if ($perPage < 1) { $perPage = 12; }
        $offset = ($page - 1) * $perPage;
        $userId = (int) ($session->get('user_id') ?? 0);
        $perfilRow = (new PerfilUsuarioModel())->where('user_id', $userId)->first() ?? [];
        $profile = [
            'user_id' => $userId,
            'nome' => $session->get('user_name') ?? 'Usuário',
            'imagem' => $perfilRow['foto_perfil'] ?? '',
        ];
        $vipModel = new NlListaUsuarioModel();
        $vip = [
            'pendentes' => (int)$vipModel->where('user_id', $userId)->where('status', 'pendente')->countAllResults(),
            'total' => (int)$vipModel->where('user_id', $userId)->countAllResults(),
            'link' => '/vip/lista',
        ];
        $db = \Config\Database::connect();
        $builder = $db->table('evt_ingressos_vendidos_tb iv');
        $builder->select('iv.ingresso_id, iv.codigo_unico, iv.status, iv.valor_pago, iv.data_compra, iv.nome_comprador, e.evento_id, e.nome AS evento_nome, e.data_inicio, e.data_fim, e.hora_abertura_portas, b.nome AS bar_nome, l.nome AS lote_nome')
                ->join('evt_eventos_tb e', 'e.evento_id = iv.evento_id', 'inner')
                ->join('form_perfil_bares_tb b', 'b.bares_id = e.bares_id', 'left')
                ->join('evt_lotes_ingressos_tb l', 'l.lote_id = iv.lote_id', 'left')
                ->where('iv.user_id', $userId);
        if ($statusFilter === 'utilizados') {
            $builder->where('iv.status', 'utilizado');
        } elseif ($statusFilter === 'antigos') {
            $builder->where('COALESCE(e.data_fim, e.data_inicio) < CURDATE()', null, false);
        } elseif ($statusFilter === 'futuros') {
            $builder->where('COALESCE(e.data_fim, e.data_inicio) >= CURDATE()', null, false);
        }
        $builder->orderBy('COALESCE(e.data_fim, e.data_inicio) DESC, iv.data_compra DESC', '', false)
                ->limit($perPage, $offset);
        $tickets = $builder->get()->getResultArray();
        $countBuilder = $db->table('evt_ingressos_vendidos_tb iv');
        $countBuilder->select('COUNT(iv.ingresso_id) AS total')
                     ->join('evt_eventos_tb e', 'e.evento_id = iv.evento_id', 'inner')
                     ->where('iv.user_id', $userId);
        if ($statusFilter === 'utilizados') {
            $countBuilder->where('iv.status', 'utilizado');
        } elseif ($statusFilter === 'antigos') {
            $countBuilder->where('COALESCE(e.data_fim, e.data_inicio) < CURDATE()', null, false);
        } elseif ($statusFilter === 'futuros') {
            $countBuilder->where('COALESCE(e.data_fim, e.data_inicio) >= CURDATE()', null, false);
        }
        $totalRow = $countBuilder->get()->getRowArray();
        $total = (int)($totalRow['total'] ?? 0);
        $lastPage = max(1, (int)ceil($total / $perPage));
        $usuarioCpf = $perfilRow['cpf'] ?? '';
        return view('dash_user/ingressos', [
            'profile' => $profile,
            'vip' => $vip,
            'tickets' => $tickets,
            'usuarioCpf' => $usuarioCpf,
            'statusFilter' => $statusFilter ?: 'todos',
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'lastPage' => $lastPage,
            ],
        ]);
    }

    public function header()
    {
        $session = session();
        if (!$session->get('logged') || $session->get('type_user') != 2) {
            return redirect()->to('/dashboard')->with('error', 'Acesso não autorizado.');
        }
        $userId = (int) ($session->get('user_id') ?? 0);
        $perfilRow = (new PerfilUsuarioModel())->where('user_id', $userId)->first() ?? [];
        $profile = [
            'user_id' => $userId,
            'nome' => $session->get('user_name') ?? 'Usuário',
            'imagem' => $perfilRow['foto_perfil'] ?? '',
        ];
        $vipModel = new NlListaUsuarioModel();
        $vip = [
            'pendentes' => (int)$vipModel->where('user_id', $userId)->where('status', 'pendente')->countAllResults(),
            'total' => (int)$vipModel->where('user_id', $userId)->countAllResults(),
            'link' => '/vip/lista',
        ];
        return view('dash_user/header_sem_admin', ['profile' => $profile, 'vip' => $vip]);
    }

    public function storiesAll()
    {
        $session = session();
        if (!$session->get('logged') || $session->get('type_user') != 2) {
            return redirect()->to('/dashboard')->with('error', 'Acesso não autorizado.');
        }
        return view('dash/stories_all');
    }

    public function posts()
    {
        $session = session();
        if (!$session->get('logged') || $session->get('type_user') != 2) {
            return redirect()->to('/dashboard')->with('error', 'Acesso não autorizado.');
        }
        return view('dash/posts');
    }

    public function storiesList()
    {
        $session = session();
        if (!$session->get('logged') || $session->get('type_user') != 2) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Acesso não autorizado']);
        }
        $userId = (int) ($session->get('user_id') ?? 0);
        $page = (int) ($this->request->getGet('page') ?? 1);
        $limit = (int) ($this->request->getGet('limit') ?? 12);
        if ($page < 1) $page = 1;
        if ($limit < 1) $limit = 12;
        $pull = $page * $limit;
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');
        $followRows = (new \App\Models\User\FollowModel())
            ->where('follower_type', 'user')
            ->where('follower_id', $userId)
            ->where('status', 'active')
            ->findAll(5000);
        $followUsers = [];
        $followBars = [];
        foreach ($followRows as $f) {
            if (($f['target_type'] ?? '') === 'user') {
                $followUsers[(int) ($f['target_id'] ?? 0)] = true;
            } elseif (($f['target_type'] ?? '') === 'bar') {
                $followBars[(int) ($f['target_id'] ?? 0)] = true;
            }
        }
        $userRows = $db->table('user_stories_tb s')
            ->select('s.story_id, s.image_url, s.user_id, s.created_at, u.name as name, u.name as poster_name, NULL as bares_id, NULL as bairro_nome, NULL as cidade_nome')
            ->join('users u', 'u.id = s.user_id', 'left')
            ->where('s.deleted_at', null)
            ->where('s.expires_at >=', $now)
            ->orderBy('s.created_at', 'DESC')
            ->limit($pull)
            ->get()->getResultArray();
        $barRows = $db->table('bar_stories_tb s')
            ->select('s.story_id, s.image_url, s.user_id, s.created_at, b.nome as name, u.name as poster_name, s.bares_id, b.bairro_nome, cid.nome as cidade_nome')
            ->join('form_perfil_bares_tb b', 'b.bares_id = s.bares_id', 'left')
            ->join('users u', 'u.id = s.user_id', 'left')
            ->join('base_cidades cid', 'cid.id = b.cidade_id', 'left')
            ->where('s.deleted_at', null)
            ->where('s.expires_at >=', $now)
            ->orderBy('s.created_at', 'DESC')
            ->limit($pull)
            ->get()->getResultArray();
        $normalizeImage = function (&$row) {
            if (!empty($row['image_url'])) {
                $val = str_replace('\\', '/', (string) $row['image_url']);
                $prefix = str_replace('\\', '/', ROOTPATH . 'public' . DIRECTORY_SEPARATOR);
                if (strpos($val, $prefix) === 0) {
                    $val = substr($val, strlen($prefix));
                }
                $val = ltrim($val, '/');
                $abs = ROOTPATH . 'public' . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $val), DIRECTORY_SEPARATOR);
                if (!is_file($abs) || @filesize($abs) === 0) {
                    $row['image_url'] = null;
                } else {
                    $row['image_url'] = $val;
                }
            }
        };
        foreach ($userRows as &$r) {
            $normalizeImage($r);
            $uid = (int) ($r['user_id'] ?? 0);
            $r['is_followed'] = !empty($followUsers[$uid]) ? 1 : 0;
        }
        unset($r);
        foreach ($barRows as &$r2) {
            $normalizeImage($r2);
            $bid = (int) ($r2['bares_id'] ?? 0);
            $r2['is_followed'] = !empty($followBars[$bid]) ? 1 : 0;
        }
        unset($r2);
        $combined = array_merge($userRows, $barRows);
        usort($combined, function($a, $b) {
            $fa = (int) ($a['is_followed'] ?? 0);
            $fb = (int) ($b['is_followed'] ?? 0);
            if ($fa !== $fb) return $fb <=> $fa;
            $ca = strtotime($a['created_at'] ?? '1970-01-01 00:00:00');
            $cb = strtotime($b['created_at'] ?? '1970-01-01 00:00:00');
            return $cb <=> $ca;
        });
        $start = ($page - 1) * $limit;
        $items = array_slice($combined, $start, $limit);
        return $this->response->setJSON($items);
    }
}
