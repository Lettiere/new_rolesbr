<?php

namespace App\Controllers\DashAdmin;

use App\Controllers\BaseController;
use App\Models\User\UserModel;
use App\Models\User\PerfilUsuarioModel;
use App\Models\Perfil\PerfilBarClientModel;
use App\Models\Eventos\EvtEventoModel;
use App\Models\Eventos\EvtIngressoVendidoModel;
use App\Models\Produtos\ProdutoModel;
use App\Models\Produtos\CardapioModel;
use App\Models\Produtos\CardapioItemModel;
use App\Models\User\UserPostModel;
use App\Models\User\UserStoryModel;
use App\Models\Produtos\TipoProdutoModel;
use App\Models\Produtos\FamiliaProdutoModel;
use App\Models\Produtos\BaseProdutoModel;

class DashAdminController extends BaseController
{
    public function index()
    {
        $session = session();
        if (!$session->get('logged')) {
            return redirect()->to('/auth/login')
                ->with('error', 'Você precisa estar logado para acessar esta página.');
        }
        if ((int)($session->get('type_user') ?? 0) !== 3) {
            $typeUser = (int)($session->get('type_user') ?? 0);
            if ($typeUser === 1) {
                return redirect()->to('/dashboard')->with('error', 'Acesso restrito ao administrador.');
            }
            return redirect()->to('/dashboard/user')->with('error', 'Acesso restrito ao administrador.');
        }

        $kpis = [
            'usuarios'        => (new UserModel())->where('deleted_at', null)->countAllResults(),
            'perfils_usuario' => (new PerfilUsuarioModel())->countAllResults(),
            'bares'           => (new PerfilBarClientModel())->where('deleted_at', null)->countAllResults(),
            'eventos'         => (new EvtEventoModel())->where('deleted_at', null)->countAllResults(),
            'ingressos'       => (new EvtIngressoVendidoModel())->where('deleted_at', null)->countAllResults(),
            'produtos'        => (new ProdutoModel())->where('deleted_at', null)->countAllResults(),
            'cardapios'       => (new CardapioModel())->countAllResults(),
            'cardapio_itens'  => (new CardapioItemModel())->countAllResults(),
            'posts'           => (new UserPostModel())->where('deleted_at', null)->countAllResults(),
            'stories'         => (new UserStoryModel())->where('deleted_at', null)->countAllResults(),
        ];

        $db = \Config\Database::connect();
        $latestUsers = $db->table('users')->select('id,name,email,created_at')->where('deleted_at', null)->orderBy('created_at', 'DESC')->limit(10)->get()->getResultArray();
        $latestBares = $db->table('form_perfil_bares_tb')->select('bares_id,nome,cidade_id,bairro_nome,created_at')->where('deleted_at', null)->orderBy('created_at', 'DESC')->limit(10)->get()->getResultArray();
        $tipoProdutos = (new TipoProdutoModel())->where('deleted_at', null)->countAllResults();
        $familiaProdutos = (new FamiliaProdutoModel())->where('deleted_at', null)->countAllResults();
        $baseProdutos = (new BaseProdutoModel())->where('deleted_at', null)->countAllResults();
        $postsToday = $db->table('user_posts_tb')->where('deleted_at', null)->where('DATE(created_at)', date('Y-m-d'))->countAllResults();
        $postsWeek = $db->table('user_posts_tb')->where('deleted_at', null)->where('YEARWEEK(created_at,1)=YEARWEEK(CURDATE(),1)', null, false)->countAllResults();
        $postsMonth = $db->table('user_posts_tb')->where('deleted_at', null)->where('YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())', null, false)->countAllResults();
        $storiesUsersToday = $db->table('user_stories_tb')->where('deleted_at', null)->where('DATE(created_at)', date('Y-m-d'))->countAllResults();
        $storiesUsersWeek = $db->table('user_stories_tb')->where('deleted_at', null)->where('YEARWEEK(created_at,1)=YEARWEEK(CURDATE(),1)', null, false)->countAllResults();
        $storiesUsersMonth = $db->table('user_stories_tb')->where('deleted_at', null)->where('YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())', null, false)->countAllResults();
        $storiesBarsToday = $db->table('bar_stories_tb')->where('deleted_at', null)->where('DATE(created_at)', date('Y-m-d'))->countAllResults();
        $storiesBarsWeek = $db->table('bar_stories_tb')->where('deleted_at', null)->where('YEARWEEK(created_at,1)=YEARWEEK(CURDATE(),1)', null, false)->countAllResults();
        $storiesBarsMonth = $db->table('bar_stories_tb')->where('deleted_at', null)->where('YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())', null, false)->countAllResults();
        $topUsersStories = $db->table('user_stories_tb')->select('user_id, COUNT(*) as total')->where('deleted_at', null)->groupBy('user_id')->orderBy('total', 'DESC')->limit(10)->get()->getResultArray();
        $topBarsStories = $db->table('bar_stories_tb')->select('bares_id, COUNT(*) as total')->where('deleted_at', null)->groupBy('bares_id')->orderBy('total', 'DESC')->limit(10)->get()->getResultArray();

        return view('dash/dash_admin', [
            'kpis' => $kpis,
            'latestUsers' => $latestUsers,
            'latestBares' => $latestBares,
            'produtoTiposCount' => $tipoProdutos,
            'produtoFamiliasCount' => $familiaProdutos,
            'produtoBasesCount' => $baseProdutos,
            'postsPeriod' => [
                'today' => $postsToday,
                'week' => $postsWeek,
                'month' => $postsMonth,
            ],
            'storiesPeriod' => [
                'users' => ['today' => $storiesUsersToday, 'week' => $storiesUsersWeek, 'month' => $storiesUsersMonth],
                'bars'  => ['today' => $storiesBarsToday,  'week' => $storiesBarsWeek,  'month' => $storiesBarsMonth],
            ],
            'topUsersStories' => $topUsersStories,
            'topBarsStories' => $topBarsStories,
        ]);
    }

    public function kpisJson()
    {
        $session = session();
        if (!$session->get('logged') || (int)($session->get('type_user') ?? 0) !== 3) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'unauthorized']);
        }
        $kpis = [
            'usuarios'        => (new \App\Models\User\UserModel())->where('deleted_at', null)->countAllResults(),
            'bares'           => (new \App\Models\Perfil\PerfilBarClientModel())->where('deleted_at', null)->countAllResults(),
            'eventos'         => (new \App\Models\Eventos\EvtEventoModel())->where('deleted_at', null)->countAllResults(),
            'ingressos'       => (new \App\Models\Eventos\EvtIngressoVendidoModel())->where('deleted_at', null)->countAllResults(),
            'produtos'        => (new \App\Models\Produtos\ProdutoModel())->where('deleted_at', null)->countAllResults(),
            'posts'           => (new \App\Models\User\UserPostModel())->where('deleted_at', null)->countAllResults(),
            'stories'         => (new \App\Models\User\UserStoryModel())->where('deleted_at', null)->countAllResults(),
        ];
        return $this->response->setJSON($kpis);
    }

    public function social()
    {
        $session = session();
        if (!$session->get('logged') || (int)($session->get('type_user') ?? 0) !== 3) {
            return redirect()->to('/dashboard')->with('error', 'Acesso restrito ao administrador.');
        }
        return view('dash/dash_admin_social');
    }

    public function highlightCreate()
    {
        $session = session();
        if (!$session->get('logged') || (int)($session->get('type_user') ?? 0) !== 3) {
            return redirect()->to('/dashboard')->with('error', 'Acesso restrito ao administrador.');
        }
        $eventos = (new \App\Models\Eventos\EvtEventoModel())
            ->select('evento_id, nome, imagem_capa')
            ->where('deleted_at', null)
            ->orderBy('created_at', 'DESC')
            ->findAll(200);
        return view('dash/dash_admin_highlight_form', ['eventos' => $eventos]);
    }

    public function highlightStore()
    {
        $session = session();
        if (!$session->get('logged') || (int)($session->get('type_user') ?? 0) !== 3) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'unauthorized']);
        }
        $eventoId = (int) ($this->request->getPost('evento_id') ?? 0);
        $startAt  = (string) ($this->request->getPost('start_at') ?? '');
        $endAt    = (string) ($this->request->getPost('end_at') ?? '');
        if ($eventoId <= 0 || !$startAt || !$endAt) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'invalid']);
        }
        $model = new \App\Models\Eventos\SponsoredHighlightModel();
        $model->insert([
            'evento_id' => $eventoId,
            'image_url' => null,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'active' => 1,
        ]);
        return redirect()->to('/dashboard/admin')->with('success', 'Destaque criado');
    }

    public function highlightActive()
    {
        $session = session();
        if (!$session->get('logged') || (int)($session->get('type_user') ?? 0) !== 3) {
            return $this->response->setJSON(null);
        }
        $now = date('Y-m-d H:i:s');
        $model = new \App\Models\Eventos\SponsoredHighlightModel();
        $row = $model->select('sponsored_highlights_tb.*, e.imagem_capa')
            ->join('evt_eventos_tb e', 'e.evento_id = sponsored_highlights_tb.evento_id', 'left')
            ->where('active', 1)
            ->where('start_at <=', $now)
            ->where('end_at >=', $now)
            ->orderBy('start_at', 'DESC')
            ->first();
        if (!$row) return $this->response->setJSON(null);
        $eventoId = (int) ($row['evento_id'] ?? 0);
        $eventoLink = $eventoId > 0 ? ('/evento/' . $eventoId) : null;
        $image = null;
        if (!empty($row['image_url'])) {
            $image = ltrim(str_replace('\\', '/', $row['image_url']), '/');
        } else {
            $image = !empty($row['imagem_capa']) ? ('/' . ltrim(str_replace('\\', '/', $row['imagem_capa']), '/')) : null;
        }
        return $this->response->setJSON([
            'evento_id' => $eventoId,
            'link' => $eventoLink,
            'image_url' => $image,
            'start_at' => $row['start_at'] ?? null,
            'end_at' => $row['end_at'] ?? null,
        ]);
    }

    public function highlightIndex()
    {
        $session = session();
        if (!$session->get('logged') || (int)($session->get('type_user') ?? 0) !== 3) {
            return redirect()->to('/dashboard')->with('error', 'Acesso restrito ao administrador.');
        }
        $model = new \App\Models\Eventos\SponsoredHighlightModel();
        $highlights = $model->select('sponsored_highlights_tb.*, e.nome as evento_nome, e.imagem_capa')
            ->join('evt_eventos_tb e', 'e.evento_id = sponsored_highlights_tb.evento_id', 'left')
            ->orderBy('start_at', 'DESC')
            ->findAll();
        return view('dash/dash_admin_highlight_list', ['highlights' => $highlights]);
    }

    public function highlightToggle($id)
    {
        $session = session();
        if (!$session->get('logged') || (int)($session->get('type_user') ?? 0) !== 3) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'unauthorized']);
        }
        $id = (int) $id;
        $model = new \App\Models\Eventos\SponsoredHighlightModel();
        $row = $model->find($id);
        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'not_found']);
        }
        $newActive = ((int)($row['active'] ?? 0) === 1) ? 0 : 1;
        $model->update($id, ['active' => $newActive]);
        return $this->response->setJSON(['active' => $newActive]);
    }

    public function highlightEdit($id)
    {
        $session = session();
        if (!$session->get('logged') || (int)($session->get('type_user') ?? 0) !== 3) {
            return redirect()->to('/dashboard')->with('error', 'Acesso restrito ao administrador.');
        }
        $id = (int) $id;
        $model = new \App\Models\Eventos\SponsoredHighlightModel();
        $highlight = $model->find($id);
        if (!$highlight) {
            return redirect()->to('/dashboard/admin/highlight/list')->with('error', 'Destaque não encontrado.');
        }
        $eventos = (new \App\Models\Eventos\EvtEventoModel())
            ->select('evento_id, nome, imagem_capa')
            ->where('deleted_at', null)
            ->orderBy('created_at', 'DESC')
            ->findAll(300);
        return view('dash/dash_admin_highlight_edit', ['highlight' => $highlight, 'eventos' => $eventos]);
    }

    public function highlightUpdate($id)
    {
        $session = session();
        if (!$session->get('logged') || (int)($session->get('type_user') ?? 0) !== 3) {
            return redirect()->to('/dashboard')->with('error', 'Acesso restrito ao administrador.');
        }
        $id = (int) $id;
        $eventoId = (int) ($this->request->getPost('evento_id') ?? 0);
        $startAt  = (string) ($this->request->getPost('start_at') ?? '');
        $endAt    = (string) ($this->request->getPost('end_at') ?? '');
        $active   = $this->request->getPost('active') ? 1 : 0;
        if ($eventoId <= 0 || !$startAt || !$endAt) {
            return redirect()->back()->with('error', 'Dados inválidos.');
        }
        $model = new \App\Models\Eventos\SponsoredHighlightModel();
        $ok = $model->update($id, [
            'evento_id' => $eventoId,
            'image_url' => null,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'active' => $active,
        ]);
        if (!$ok) {
            return redirect()->back()->with('error', 'Falha ao atualizar.');
        }
        return redirect()->to('/dashboard/admin/highlight/list')->with('success', 'Destaque atualizado.');
    }

    public function highlightDelete($id)
    {
        $session = session();
        if (!$session->get('logged') || (int)($session->get('type_user') ?? 0) !== 3) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'unauthorized']);
        }
        $id = (int) $id;
        $model = new \App\Models\Eventos\SponsoredHighlightModel();
        $row = $model->find($id);
        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'not_found']);
        }
        $model->delete($id);
        return $this->response->setJSON(['success' => true]);
    }
}
