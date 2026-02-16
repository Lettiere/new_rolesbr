<?php

namespace App\Controllers;

use App\Libraries\GeoFilter;
use App\Models\User\UserModel;
use App\Models\User\PerfilUsuarioModel;
use App\Models\User\UserPostModel;
use App\Models\User\UserPostTextModel;
use App\Models\Perfil\PerfilBarClientModel;
use App\Models\User\UserPostCommentModel;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\QrCode;

class Home extends BaseController
{
    public function index(): string
    {
        return view('home');
    }

    public function post($id)
    {
        $postModel = new UserPostModel();
        $post = $postModel->find($id);
        
        if (!$post) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Author
        $userModel = new UserModel();
        $author = $userModel->find($post['user_id']);
        $perfilModel = new PerfilUsuarioModel();
        $perfil = $perfilModel->where('user_id', $post['user_id'])->first();

        // Caption
        $textModel = new UserPostTextModel();
        $text = $textModel->where('post_id', $id)->orderBy('created_at', 'DESC')->first();
        
        // Image Path Logic
        $cleanUrl = str_replace('\\', '/', $post['image_url']);
        $publicRoot = str_replace('\\', '/', ROOTPATH . 'public/');
        if (strpos($cleanUrl, $publicRoot) === 0) {
            $cleanUrl = substr($cleanUrl, strlen($publicRoot));
        }
        $cleanUrl = ltrim($cleanUrl, '/');
        if (strpos($cleanUrl, 'public/') === 0) {
            $cleanUrl = substr($cleanUrl, 7);
        }

        $data = [
            'author_name' => $author['name'] ?? 'Usuário',
            'author_avatar' => $perfil['foto_perfil'] ?? "https://ui-avatars.com/api/?name=" . urlencode($author['name'] ?? 'U'),
            'caption' => $text['text_body'] ?? '',
            'image_url' => base_url($cleanUrl),
            'image_full_url' => base_url($cleanUrl),
        ];

        return view('public_post', ['post' => $data]);
    }

    public function eventos(): string
    {
        return view('eventos');
    }

    public function ingressosIndex(): string
    {
        return view('ingressos/index_ingressos');
    }

    public function buscaEventos()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('evt_eventos_tb e');
        $userId = session()->get('logged') ? (int)session()->get('user_id') : 0;
        
        $builder->select("
            e.evento_id,
            e.nome,
            e.descricao,
            e.data_inicio,
            e.status,
            e.imagem_capa,
            e.local_customizado,
            b.bares_id,
            b.nome as bar_nome,
            b.endereco as bar_endereco,
            b.nome_na_lista,
            est.nome as estado_nome,
            cid.nome as cidade_nome,
            bai.nome as bairro_nome,
            (SELECT COUNT(*) FROM evt_ingressos_vendidos_tb iv WHERE iv.evento_id = e.evento_id) as vendidos,
            (SELECT COUNT(*) FROM evt_interesse_evento_tb ie WHERE ie.evento_id = e.evento_id AND ie.user_id = {$userId} AND ie.type = 'like') as is_liked,
            (SELECT COUNT(*) FROM evt_interesse_evento_tb ie WHERE ie.evento_id = e.evento_id AND ie.user_id = {$userId} AND ie.type = 'going') as is_going,
            (SELECT COUNT(*) FROM evt_interesse_evento_tb ie WHERE ie.evento_id = e.evento_id AND ie.type = 'going') as going_count
        ");
        $builder->join('form_perfil_bares_tb b', 'b.bares_id = e.bares_id', 'inner');
        $builder->join('base_cidades cid', 'cid.id = b.cidade_id', 'left');
        $builder->join('base_estados est', 'est.id = b.estado_id', 'left');
        $builder->join('base_bairros bai', 'bai.id = b.bairro_id', 'left');
        $builder->join('evt_tipo_evento_tb te', 'te.tipo_evento_id = e.tipo_evento_id', 'left');
        $builder->whereIn('e.status', ['publicado','programado']);
        $builder->where('e.data_inicio >= NOW()', null, false);
        // filtro por tipo de evento (evt_tipo_evento_tb)
        $tipoEventoId = (int) ($this->request->getGet('tipo_evento_id') ?? 0);
        if ($tipoEventoId > 0) {
            $builder->where('e.tipo_evento_id', $tipoEventoId);
        }
        // filtro por tipo de bar (form_perfil_tipo_bar_tb)
        $tipoBarId = (int) ($this->request->getGet('tipo_bar_id') ?? 0);
        if ($tipoBarId > 0) {
            $builder->where('b.tipo_bar', $tipoBarId);
        }
        GeoFilter::apply($builder, 'b');
        GeoFilter::restrict($builder, 'b');

        $q = $this->request->getGet('q');
        if (!empty($q)) {
            $builder->groupStart()
                ->like('e.nome', $q)
                ->orLike('e.slug', $q)
                ->orLike('e.descricao', $q)
                ->orLike('te.nome', $q)
                ->orLike('b.nome', $q)
                ->orLike('b.endereco', $q)
                ->orLike('cid.nome', $q)
                ->orLike('bai.nome', $q)
                ->orLike('est.nome', $q)
            ->groupEnd();
            if (is_numeric($q)) {
                $builder->orWhere('e.evento_id', (int) $q)    
                        ->orWhere('b.tipo_bar', (int) $q)
                        ->orWhere('e.tipo_evento_id', (int) $q);
            }
        }
        $estadoId = (int) ($this->request->getGet('estado_id') ?? 0);
        $cidadeId = (int) ($this->request->getGet('cidade_id') ?? 0);
        $bairroId = (int) ($this->request->getGet('bairro_id') ?? 0);
        if ($estadoId > 0) {
            $builder->where('b.estado_id', $estadoId);
        }
        if ($cidadeId > 0) {
            $builder->where('b.cidade_id', $cidadeId);
        }
        if ($bairroId > 0) {
            $builder->groupStart()
                ->where('b.bairro_id', $bairroId)
                ->orWhere("(b.bairro_id IS NULL AND b.bairro_nome = (SELECT nome FROM base_bairros WHERE id = {$bairroId}))", null, false)
            ->groupEnd();
        }

        $builder->where('e.deleted_at', null);
        $builder->orderBy('e.data_inicio', 'ASC');
        $builder->groupBy('e.evento_id');

        $page = (int) ($this->request->getGet('page') ?? 1);  
        $limit = (int) ($this->request->getGet('limit') ?? 12);
        if ($page < 1) $page = 1;
        if ($limit < 1) $limit = 12;
        $offset = ($page - 1) * $limit;
        $builder->limit($limit, $offset);

        $events = $builder->get()->getResultArray();

        return $this->response->setJSON($events);
    }
    
    public function eventoLotes($eventoId)
    {
        $key = 'evento_lotes_' . (int) $eventoId;
        $cache = service('cache');
        try {
            $db = \Config\Database::connect();
            $rows = $db->table('evt_lotes_ingressos_tb l')
                ->select('l.lote_id, l.evento_id, l.nome, l.preco, l.quantidade_total, l.quantidade_vendida, l.data_inicio_vendas, l.data_fim_vendas, l.ativo')
                ->where('l.evento_id', (int) $eventoId)
                ->where('l.ativo', 1)
                ->orderBy('l.preco', 'ASC')
                ->get()->getResultArray();
            $this->response->setJSON(['items' => $rows]);
            $cache?->save($key, ['items' => $rows], 60);
            return $this->response;
        } catch (\Throwable $e) {
            $cached = $cache?->get($key);
            if (is_array($cached)) {
                return $this->response->setJSON($cached);
            }
            return $this->response->setStatusCode(500)->setJSON(['error' => 'service_unavailable']);
        }
    }

    public function publicUserByCpf()
    {
        $cpf = preg_replace('/\\D+/', '', (string) ($this->request->getGet('cpf') ?? ''));
        if ($cpf === '') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'CPF obrigatório']);
        }
        $perfilModel = new PerfilUsuarioModel();
        $perfil = $perfilModel->where('cpf', $cpf)->first();
        if (!$perfil) {
            return $this->response->setJSON(['exists' => false]);
        }
        $userModel = new UserModel();
        $user = $userModel->find((int) ($perfil['user_id'] ?? 0));
        if (!$user) {
            return $this->response->setJSON(['exists' => false]);
        }
        return $this->response->setJSON([
            'exists' => true,
            'user' => [
                'id' => (int) ($user['id'] ?? 0),
                'name' => $user['name'] ?? '',
                'email' => $user['email'] ?? ''
            ]
        ]);
    }

    public function publicRegisterUser()
    {
        $name = trim((string) ($this->request->getPost('name') ?? ''));
        $email = strtolower(trim((string) ($this->request->getPost('email') ?? '')));
        $password = (string) ($this->request->getPost('password') ?? '');
        $passwordConfirm = (string) ($this->request->getPost('password_confirm') ?? '');
        $cpf = preg_replace('/\\D+/', '', (string) ($this->request->getPost('cpf') ?? ''));
        $telefone = trim((string) ($this->request->getPost('telefone') ?? ''));
        $rg = trim((string) ($this->request->getPost('rg') ?? ''));
        $dataNascimento = (string) ($this->request->getPost('data_nascimento') ?? '');
        $generoId = (int) ($this->request->getPost('genero_id') ?? 0);
        $estadoId = (int) ($this->request->getPost('estado_id') ?? 0);
        $cidadeId = (int) ($this->request->getPost('cidade_id') ?? 0);
        $bairroId = (int) ($this->request->getPost('bairro_id') ?? 0);
        $ruaId = (int) ($this->request->getPost('rua_id') ?? 0);
        $bairroNome = trim((string) ($this->request->getPost('bairro_nome') ?? ''));
        $lat = (string) ($this->request->getPost('location_lat') ?? '');
        $lng = (string) ($this->request->getPost('location_lng') ?? '');
        if ($name === '' || strlen($name) < 3) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Nome inválido']);
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'E-mail inválido']);
        }
        if ($password === '' || strlen($password) < 8 || $password !== $passwordConfirm) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Senha inválida']);
        }
        if ($cpf === '' || $telefone === '') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'CPF e telefone obrigatórios']);
        }
        $userModel = new UserModel();
        if ($userModel->where('email', $email)->first()) {
            return $this->response->setStatusCode(409)->setJSON(['error' => 'E-mail já cadastrado']);
        }
        $userId = $userModel->insert([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'client',
            'type_user' => 2,
            'is_active' => 1
        ]);
        if (!$userId) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Erro ao criar usuário']);
        }
        $perfilModel = new PerfilUsuarioModel();
        try {
            $perfilModel->insert([
                'user_id' => (int) $userId,
                'cpf' => $cpf,
                'telefone' => $telefone,
                'rg' => $rg ?: null,
                'data_nascimento' => $dataNascimento ?: null,
                'genero_id' => $generoId ?: null,
                'estado_id' => $estadoId ?: null,
                'cidade_id' => $cidadeId ?: null,
                'bairro_id' => $bairroId ?: null,
                'rua_id' => $ruaId ?: null,
                'bairro_nome' => $bairroNome ?: null,
                'location_lat' => $lat !== '' ? $lat : null,
                'location_lng' => $lng !== '' ? $lng : null,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(409)->setJSON(['error' => 'CPF já cadastrado']);
        }
        return $this->response->setJSON(['success' => true, 'user_id' => (int) $userId]);
    }

    public function venderIngressoPublic()
    {
        $eventoId = (int) ($this->request->getPost('evento_id') ?? 0);
        $loteId = (int) ($this->request->getPost('lote_id') ?? 0);
        $cpf = preg_replace('/\\D+/', '', (string) ($this->request->getPost('cpf') ?? ''));
        $nome = trim((string) ($this->request->getPost('nome') ?? ''));
        $email = strtolower(trim((string) ($this->request->getPost('email') ?? '')));
        if ($eventoId <= 0 || $loteId <= 0 || $cpf === '') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Dados inválidos']);
        }
        $db = \Config\Database::connect();
        $lote = $db->table('evt_lotes_ingressos_tb')->where('lote_id', $loteId)->where('evento_id', $eventoId)->where('ativo', 1)->get()->getRowArray();
        if (!$lote) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Lote inválido']);
        }
        $perfilModel = new PerfilUsuarioModel();
        $perfil = $perfilModel->where('cpf', $cpf)->first();
        if (!$perfil) {
            if ($nome === '' || $email === '') {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Usuário não encontrado']);
            }
            $userModel = new UserModel();
            if ($userModel->where('email', $email)->first()) {
                $u = $userModel->where('email', $email)->first();
                $perfilModel->insert(['user_id' => (int) ($u['id'] ?? 0), 'cpf' => $cpf, 'telefone' => $this->request->getPost('telefone') ?? '']);
                $perfil = $perfilModel->where('cpf', $cpf)->first();
            } else {
                $uid = $userModel->insert([
                    'name' => $nome,
                    'email' => $email,
                    'password' => password_hash(bin2hex(random_bytes(6)), PASSWORD_DEFAULT),
                    'role' => 'client',
                    'type_user' => 2,
                    'is_active' => 1
                ]);
                $perfilModel->insert(['user_id' => (int) $uid, 'cpf' => $cpf, 'telefone' => $this->request->getPost('telefone') ?? '']);
                $perfil = $perfilModel->where('cpf', $cpf)->first();
            }
        }
        $codigo = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        $db->table('evt_ingressos_vendidos_tb')->insert([
            'evento_id' => $eventoId,
            'lote_id' => $loteId,
            'user_id' => (int) ($perfil['user_id'] ?? 0),
            'nome_comprador' => $nome ?: ($this->request->getPost('nome_comprador') ?? ''),
            'email_comprador' => $email ?: ($this->request->getPost('email_comprador') ?? ''),
            'codigo_unico' => $codigo,
            'status' => 'pago',
            'valor_pago' => $lote['preco'],
            'data_compra' => date('Y-m-d H:i:s'),
        ]);
        return $this->response->setJSON(['success' => true, 'codigo' => $codigo]);
    }

    public function ingressoQr($codigo)
    {
        $db = \Config\Database::connect();
        $row = $db->table('evt_ingressos_vendidos_tb v')
            ->select('v.codigo_unico, e.evento_id, e.nome as evento_nome, l.lote_id, l.nome as lote_nome')
            ->join('evt_eventos_tb e', 'e.evento_id = v.evento_id', 'left')
            ->join('evt_lotes_ingressos_tb l', 'l.lote_id = v.lote_id', 'left')
            ->where('v.codigo_unico', $codigo)
            ->get()->getRowArray();
        $payload = [
            'codigo' => $codigo,
            'evento' => $row['evento_nome'] ?? '',
            'lote' => $row['lote_nome'] ?? ''
        ];
        $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $qrController = new \App\Controllers\DashClient\Ingressos\IngressoController();
        $qrOut = $qrController->gerarQrCode($payloadJson);
        if ($qrOut instanceof \CodeIgniter\HTTP\ResponseInterface) {
            $resp = $qrOut;
        } else {
            $resp = $this->response->setHeader('Content-Type', 'image/png')->setBody((string) $qrOut);
        }
        $download = (string) ($this->request->getGet('download') ?? '');
        if ($download === '1') {
            $evtSlug = preg_replace('/[^a-z0-9]+/i','-', strtolower($row['evento_nome'] ?? 'evento'));
            $loteSlug = preg_replace('/[^a-z0-9]+/i','-', strtolower($row['lote_nome'] ?? 'lote'));
            $fname = "ingresso_{$evtSlug}_{$loteSlug}_{$codigo}.png";
            $resp = $resp->setHeader('Content-Disposition', 'attachment; filename="'.$fname.'"');
        }
        return $resp;
    }

    public function eventosCategorias()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('evt_tipo_evento_tb t');
        $builder->select('t.tipo_evento_id, t.nome, COUNT(e.evento_id) as qtd');
        $builder->join('evt_eventos_tb e', 'e.tipo_evento_id = t.tipo_evento_id AND e.deleted_at IS NULL', 'left');
        $builder->where('t.ativo', 1);
        $builder->groupBy('t.tipo_evento_id, t.nome');
        $builder->having('qtd >', 0);
        $builder->orderBy('t.nome', 'ASC');
        $tipos = $builder->get()->getResultArray();
        return $this->response->setJSON($tipos);
    }

    public function tiposBarPublic()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('form_perfil_tipo_bar_tb t');
        $builder->select('t.tipo_bar_id, t.nome, t.categoria, COUNT(b.bares_id) AS qtd');
        $builder->join('form_perfil_bares_tb b', 'b.tipo_bar = t.tipo_bar_id', 'inner');
        $builder->where('t.ativo', 1);
        $builder->where('b.deleted_at', null);
        $builder->groupBy('t.tipo_bar_id, t.nome, t.categoria');
        $builder->having('qtd >', 0);
        $builder->orderBy('t.nome', 'ASC');
        $rows = $builder->get()->getResultArray();
        // Retornar apenas os campos esperados no frontend
        $out = array_map(function($r){
            return [
                'tipo_bar_id' => (int) ($r['tipo_bar_id'] ?? 0),
                'nome' => (string) ($r['nome'] ?? ''),
                'categoria' => (string) ($r['categoria'] ?? ''),
            ];
        }, $rows);
        return $this->response->setJSON($out);
    }

    public function tiposProdutoPublic()
    {
        $db = \Config\Database::connect();
        $rows = $db->table('prod_tipo_produtos_tb')
            ->select('tipo_id, nome')
            ->where('ativo', 1)
            ->orderBy('nome', 'ASC')
            ->get()->getResultArray();
        return $this->response->setJSON($rows);
    }

    public function geoEstadosBares()
    {
        $db = \Config\Database::connect();
        $rows = $db->table('form_perfil_bares_tb b')
            ->select('est.id, est.nome')
            ->join('base_estados est', 'est.id = b.estado_id', 'inner')
            ->where('b.deleted_at', null)
            ->groupBy('est.id, est.nome')
            ->orderBy('est.nome', 'ASC')
            ->get()->getResultArray();
        return $this->response->setJSON($rows);
    }

    public function geoCidadesBares($estadoId)
    {
        $db = \Config\Database::connect();
        $rows = $db->table('form_perfil_bares_tb b')
            ->select('cid.id, cid.nome')
            ->join('base_cidades cid', 'cid.id = b.cidade_id', 'inner')
            ->where('b.deleted_at', null)
            ->where('b.estado_id', (int) $estadoId)
            ->groupBy('cid.id, cid.nome')
            ->orderBy('cid.nome', 'ASC')
            ->get()->getResultArray();
        return $this->response->setJSON($rows);
    }

    public function geoBairrosBares($cidadeId)
    {
        $db = \Config\Database::connect();
        $sql = "
            SELECT DISTINCT bai.id, bai.nome
            FROM form_perfil_bares_tb b
            INNER JOIN base_bairros bai
                ON bai.cidade_id = b.cidade_id
               AND (
                    bai.id = b.bairro_id
                    OR (b.bairro_id IS NULL AND bai.nome = b.bairro_nome)
               )
            WHERE b.deleted_at IS NULL
              AND b.cidade_id = :cidadeId:
            ORDER BY bai.nome ASC
        ";
        $rows = $db->query($sql, ['cidadeId' => (int) $cidadeId])->getResultArray();
        return $this->response->setJSON($rows);
    }

    public function evento($id)
    {
        $db = \Config\Database::connect();
        $evento = $db->table('evt_eventos_tb e')
            ->select('e.*, b.nome as bar_nome, b.endereco as bar_endereco, b.nome_na_lista, b.imagem as bar_imagem, cid.nome as cidade_nome, bai.nome as bairro_nome')
            ->join('form_perfil_bares_tb b', 'b.bares_id = e.bares_id', 'left')
            ->join('base_cidades cid', 'cid.id = b.cidade_id', 'left')
            ->join('base_bairros bai', 'bai.id = b.bairro_id', 'left')
            ->where('e.evento_id', (int) $id)
            ->where('e.deleted_at', null)
            ->get()->getRowArray();
        if (!$evento) {
            return view('errors/html/error_404');
        }
        $uri = service('uri');
        $seg2 = (string) ($uri->getSegment(2) ?? '');
        $makeSlug = function($s){ $s = iconv('UTF-8','ASCII//TRANSLIT',$s); $s = strtolower($s); $s = preg_replace('/[^a-z0-9]+/i','-',$s); $s = trim($s,'-'); return $s ?: 'evento'; };
        if (!preg_match('/^\d+-/', $seg2)) {
            $slug = $makeSlug($evento['nome'] ?? 'evento');
            $qs = http_build_query($this->request->getGet() ?? []);
            $to = site_url('evento/'.((int)$id).'-'.$slug) . ($qs ? ('?'.$qs) : '');
            return redirect()->to($to);
        }
        $barId = (int) ($evento['bares_id'] ?? 0);
        $fotoModel = new \App\Models\Perfil\FotoBarModel();
        $fotos = $fotoModel->where('bares_id', $barId)->findAll();
        $cardapioModel = new \App\Models\Produtos\CardapioModel();
        $itemModel = new \App\Models\Produtos\CardapioItemModel();
        $cardapios = $cardapioModel->where('bares_id', $barId)->where('status', 'ativo')->findAll();
        $cardapioItens = [];
        foreach ($cardapios as $c) {
            $rows = $itemModel
                ->select('prod_cardapio_itens_tb.*, p.nome as produto_nome, p.unidade, p.preco as produto_preco, p.tipo_produto, p.subtipo_bebida, f.nome as familia_nome')
                ->join('prod_produtos_tb p', 'p.prod_id = prod_cardapio_itens_tb.prod_id', 'left')
                ->join('prod_familia_produtos_tb f', 'f.familia_id = p.familia_id', 'left')
                ->where('cardapio_id', $c['cardapio_id'])
                ->orderBy('ordem', 'ASC')
                ->findAll();
            $cardapioItens[$c['cardapio_id']] = $rows;
        }
        $prox = $db->table('evt_eventos_tb e')
            ->select('e.evento_id, e.nome, e.data_inicio, e.imagem_capa')
            ->where('e.bares_id', $barId)
            ->where('e.deleted_at', null)
            ->where('e.data_inicio >=', date('Y-m-d H:i:s'))
            ->orderBy('e.data_inicio', 'ASC')
            ->limit(6)
            ->get()->getResultArray();
        $lotes = $db->table('evt_lotes_ingressos_tb l')
            ->select('l.*, (SELECT COUNT(*) FROM evt_ingressos_vendidos_tb v WHERE v.lote_id = l.lote_id AND v.deleted_at IS NULL) AS vendidos')
            ->where('l.evento_id', (int) $id)
            ->where('l.ativo', 1)
            ->where('l.deleted_at', null)
            ->where('(l.data_inicio_vendas IS NULL OR l.data_inicio_vendas <= NOW())', null, false)
            ->where('(l.data_fim_vendas IS NULL OR l.data_fim_vendas >= NOW())', null, false)
            ->orderBy('l.preco', 'ASC')
            ->get()->getResultArray();
        return view('evento', [
            'evento' => $evento,
            'fotos' => $fotos,
            'cardapios' => $cardapios,
            'cardapio_itens' => $cardapioItens,
            'proximos' => $prox,
            'lotes' => $lotes
        ]);
    }

    public function eventosProximos()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('evt_eventos_tb e');
        $builder->select("
            e.evento_id,
            e.nome,
            e.descricao,
            e.data_inicio,
            e.status,
            e.imagem_capa,
            e.local_customizado,
            b.nome as bar_nome,
            b.endereco as bar_endereco,
            cid.nome as cidade_nome,
            bai.nome as bairro_nome,
            (SELECT COUNT(*) FROM evt_ingressos_vendidos_tb iv WHERE iv.evento_id = e.evento_id) as vendidos
        ");
        $builder->join('form_perfil_bares_tb b', 'b.bares_id = e.bares_id', 'left');
        $builder->join('base_cidades cid', 'cid.id = b.cidade_id', 'left');
        $builder->join('base_bairros bai', 'bai.id = b.bairro_id', 'left');
        GeoFilter::apply($builder, 'b');
        GeoFilter::restrict($builder, 'b');
        $builder->where('e.deleted_at', null);
        $builder->whereIn('e.status', ['publicado','programado']);
        $period = strtolower($this->request->getGet('period') ?? '7d');
        $nowTs = time();
        $startTs = $nowTs;
        $endTs = $nowTs + 7*24*3600;
        if ($period === '24h') {
            $endTs = $nowTs + 24*3600;
        } elseif ($period === '30d') {
            $endTs = $nowTs + 30*24*3600;
        } elseif ($period === 'weekend') {
            $dow = (int) date('N', $nowTs);
            $daysUntilSaturday = (6 - $dow + 7) % 7;
            $startTs = strtotime('today', $nowTs) + $daysUntilSaturday * 86400;
            $endTs = $startTs + 2*86400 - 1;
        } elseif ($period === '7d') {
            $endTs = $nowTs + 7*24*3600;
        }
        $start = date('Y-m-d H:i:s', $startTs);
        $end = date('Y-m-d H:i:s', $endTs);
        $builder->where('e.data_inicio >=', $start);
        $builder->where('e.data_inicio <=', $end);
        $builder->orderBy('e.data_inicio', 'ASC');
        $limit = (int) ($this->request->getGet('limit') ?? 10);
        if ($limit < 1) $limit = 10;
        $builder->limit($limit);
        $rows = $builder->get()->getResultArray();
        return $this->response->setJSON($rows);
    }

    public function cardapioShow($id)
    {
        $cardapio = (new \App\Models\Produtos\CardapioModel())->find((int) $id);
        if (!$cardapio) {
            return view('errors/html/error_404');
        }
        $barId = (int) ($cardapio['bares_id'] ?? 0);
        $barNome = '';
        if ($barId) {
            $bar = (new \App\Models\Perfil\PerfilBarClientModel())->find($barId);
            $barNome = (string) ($bar['nome'] ?? '');
        }
        $makeSlug = function($s){ $s = iconv('UTF-8','ASCII//TRANSLIT',$s); $s = strtolower($s); $s = preg_replace('/[^a-z0-9]+/i','-',$s); $s = trim($s,'-'); return $s ?: 'bar'; };
        $slug = $makeSlug($barNome);
        $to = site_url('bar/'.$barId.($slug?('-'.$slug):'')).'?tab=cardapio';
        return redirect()->to($to);
    }

    public function produtoShow($id)
    {
        $produto = (new \App\Models\Produtos\ProdutoModel())->find((int) $id);
        if (!$produto) {
            return view('errors/html/error_404');
        }
        $barId = (int) ($produto['bares_id'] ?? 0);
        $bar = $barId ? (new \App\Models\Perfil\PerfilBarClientModel())->find($barId) : null;
        $fotoModel = new \App\Models\Produtos\FotoProdutoModel();
        $foto = $fotoModel->where('prod_id', (int)$id)->orderBy('ordem','ASC')->first();
        return view('produto', [
            'produto' => $produto,
            'bar' => $bar,
            'foto' => $foto
        ]);
    }

    public function serveUpload($path)
    {
        $safe = str_replace(['..','\\'], ['','/'], $path);
        $safe = ltrim($safe, '/');
        $full = ROOTPATH . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $safe);
        if (!is_file($full)) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $full) ?: 'application/octet-stream';
        finfo_close($finfo);
        $body = file_get_contents($full);
        return $this->response->setHeader('Content-Type', $mime)->setBody($body);
    }

    public function eventosDestaques()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('evt_eventos_tb evt');
        $builder->select("
            evt.evento_id,
            evt.imagem_capa,
            evt.nome AS evento_nome,
            perfil.nome AS bar_nome,
            evt.data_inicio,
            evt.hora_abertura_portas,
            perfil.bairro_nome AS bairro_nome,
            cidades.nome AS cidade_nome,
            perfil.endereco,
            perfil.imagem AS bar_imagem
        ");
        $builder->join('form_perfil_bares_tb perfil', 'perfil.bares_id = evt.bares_id', 'inner');
        $builder->join('base_cidades cidades', 'cidades.id = perfil.cidade_id', 'left');
        $builder->join('form_perfil_tipo_bar_tb tipo', 'tipo.tipo_bar_id = perfil.tipo_bar', 'left');
        $builder->whereIn('evt.status', ['publicado','programado']);
        $builder->where('evt.data_inicio >= NOW()', null, false);
        $builder->where('evt.deleted_at', null);
        $limit = (int) ($this->request->getGet('limit') ?? 12);
        if ($limit < 1) $limit = 12;
        \App\Libraries\GeoFilter::apply($builder, 'perfil');
        \App\Libraries\GeoFilter::restrict($builder, 'perfil');
        $builder->orderBy('evt.data_inicio', 'ASC');
        $builder->limit($limit);
        $rows = $builder->get()->getResultArray();
        if (count($rows) < 2) {
            $sql = "
                (SELECT 
                    evt.evento_id,
                    evt.imagem_capa,
                    evt.nome AS evento_nome,
                    perfil.nome AS bar_nome,
                    evt.data_inicio,
                    evt.hora_abertura_portas,
                    perfil.bairro_nome AS bairro_nome,
                    cidades.nome AS cidade_nome,
                    perfil.endereco,
                    perfil.imagem AS bar_imagem
                 FROM evt_eventos_tb evt
                 INNER JOIN form_perfil_bares_tb perfil ON perfil.bares_id = evt.bares_id
                 LEFT JOIN base_cidades cidades ON cidades.id = perfil.cidade_id
                 WHERE evt.status IN ('publicado','programado')
                   AND evt.data_inicio >= NOW()
                   AND evt.deleted_at IS NULL
                 ORDER BY evt.data_inicio ASC
                 LIMIT 6)
                UNION ALL
                (SELECT 
                    evt.evento_id,
                    evt.imagem_capa,
                    evt.nome AS evento_nome,
                    perfil.nome AS bar_nome,
                    evt.data_inicio,
                    evt.hora_abertura_portas,
                    perfil.bairro_nome AS bairro_nome,
                    cidades.nome AS cidade_nome,
                    perfil.endereco,
                    perfil.imagem AS bar_imagem
                 FROM evt_eventos_tb evt
                 INNER JOIN form_perfil_bares_tb perfil ON perfil.bares_id = evt.bares_id
                 LEFT JOIN base_cidades cidades ON cidades.id = perfil.cidade_id
                 WHERE evt.status IN ('publicado','programado')
                   AND evt.data_inicio >= NOW()
                   AND evt.deleted_at IS NULL
                 ORDER BY RAND()
                 LIMIT 6)
            ";
            $rows = $db->query($sql)->getResultArray();
        }
        $uniq = [];
        foreach ($rows as $r) {
            $id = $r['evento_id'] ?? null;
            if ($id !== null) {
                $uniq[$id] = $r;
            }
        }
        $rows = array_values($uniq);
        if (count($rows) > 1) {
            shuffle($rows);
        }
        $limitOut = (int) ($this->request->getGet('limit') ?? 12);
        if ($limitOut < 1) $limitOut = 12;
        if (count($rows) > $limitOut) {
            $rows = array_slice($rows, 0, $limitOut);
        }
        return $this->response->setJSON($rows);
    }

    public function postsPublic()
    {
        $limit  = (int) ($this->request->getGet('limit') ?? 30);
        $offset = (int) ($this->request->getGet('offset') ?? 0);
        $filterUserId = (int) ($this->request->getGet('filter_user_id') ?? 0);
        
        if ($limit < 1) $limit = 30;
        if ($limit > 30) $limit = 30;
        if ($offset < 0) $offset = 0;
        
        $userId = (int) (session()->get('user_id') ?? 0);
        $model = new UserPostModel();
        
        if ($filterUserId > 0) {
            $model->where('user_id', $filterUserId);
        }
        
        $posts = $model->orderBy('created_at', 'DESC')->findAll($limit, $offset);
        
        // --- Likes Batch Query ---
        $postIds = array_column($posts, 'post_id');
        $likesMap = [];
        $likedByMeMap = [];

        if (!empty($postIds)) {
            $likeModel = new \App\Models\User\UserPostLikeModel();
            
            // Count likes
            $counts = $likeModel->select('post_id, COUNT(*) as total')
                                ->whereIn('post_id', $postIds)
                                ->groupBy('post_id')
                                ->findAll();
            foreach ($counts as $c) {
                $likesMap[$c['post_id']] = (int)$c['total'];
            }

            // Check if liked by current user
            if ($userId) {
                $myLikes = $likeModel->select('post_id')
                                     ->where('user_id', $userId)
                                     ->whereIn('post_id', $postIds)
                                     ->findAll();
                foreach ($myLikes as $ml) {
                    $likedByMeMap[$ml['post_id']] = true;
                }
            }
        }
        // -------------------------

        $textModel = new UserPostTextModel();
        $userIds = [];
        $barIds = [];
        $followUsers = [];
        $followBars = [];
        if ($userId) {
            $follows = (new \App\Models\User\FollowModel())
                ->where('follower_type', 'user')
                ->where('follower_id', $userId)
                ->where('status', 'active')
                ->findAll(5000);
            foreach ($follows as $f) {
                if (($f['target_type'] ?? '') === 'user') {
                    $followUsers[(int) ($f['target_id'] ?? 0)] = true;
                } elseif (($f['target_type'] ?? '') === 'bar') {
                    $followBars[(int) ($f['target_id'] ?? 0)] = true;
                }
            }
        }
        foreach ($posts as &$p) {
            $text = $textModel->where('post_id', (int) $p['post_id'])->orderBy('created_at', 'DESC')->first();
            $p['caption'] = $text['text_body'] ?? ($p['caption'] ?? null);
            
            // Add like info
            $pid = $p['post_id'];
            $p['likes_count'] = $likesMap[$pid] ?? 0;
            $p['liked_by_me'] = isset($likedByMeMap[$pid]);

            $uid = (int) ($p['user_id'] ?? 0);
            if ($uid) $userIds[$uid] = true;
            $barId = (int) ($p['owner_bar_id'] ?? 0);
            if ($barId) $barIds[$barId] = true;
            if (!empty($p['image_url'])) {
                // Logic aligned with UserPostController to ensure consistency between Dashboard and Profile
                // Use relative paths and let the browser/view handle the base URL
                
                // 1. Clean path separators
            $cleanUrl = str_replace('\\', '/', $p['image_url']);
            
            // Fix absolute paths (legacy/bugged data)
            $publicRoot = str_replace('\\', '/', ROOTPATH . 'public/');
            if (strpos($cleanUrl, $publicRoot) === 0) {
                $cleanUrl = substr($cleanUrl, strlen($publicRoot));
            }

            $cleanUrl = ltrim($cleanUrl, '/');
            
            // Fix common path issues (remove public/ prefix if present)
            if (strpos($cleanUrl, 'public/') === 0) {
                $cleanUrl = substr($cleanUrl, 7);
            }
            
            // 2. Check existence using ROOTPATH . 'public' (Standard CI4 structure)
                $imagePath = ROOTPATH . 'public' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $cleanUrl);
                
                // 3. Fallback: If not found, try FCPATH (sometimes different)
                if (!is_file($imagePath)) {
                     $imagePath = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $cleanUrl);
                }

                // 4. Set image_url. 
                // Note: We do NOT set image_abs, forcing the frontend to use the relative image_url.
                // This matches UserPostController/Profile behavior which is reported as working.
                $p['image_url'] = $cleanUrl;
                $p['image_abs'] = null; // Force relative path usage in frontend
            }
            $p['is_followed'] = (!empty($followUsers[$uid]) || (!empty($followBars[$barId]))) ? 1 : 0;
        }
        unset($p);
        $userMap = [];
        $avatarMap = [];
        $barIds = $barIds ?? [];
        $barMap = [];
        if (!empty($userIds)) {
            $ids = array_keys($userIds);
            $users = (new UserModel())->whereIn('id', $ids)->findAll();
            foreach ($users as $u) {
                $userMap[(int) $u['id']] = ['name' => $u['name'] ?? 'Usuário'];
            }
            $perfis = (new PerfilUsuarioModel())->whereIn('user_id', $ids)->findAll();
            foreach ($perfis as $pf) {
                $fp = $pf['foto_perfil'] ?? null;
                if ($fp === 'null' || $fp === '' || $fp === false) $fp = null;
                $avatarMap[(int) $pf['user_id']] = $fp;
            }
        }
        if (!empty($barIds)) {
            $ids = array_keys($barIds);
            $bars = (new PerfilBarClientModel())->whereIn('bares_id', $ids)->findAll();
            foreach ($bars as $b) {
                $id = (int) ($b['bares_id'] ?? 0);
                $nome = (string) ($b['nome'] ?? '');
                $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($nome));
                $slug = trim($slug, '-');
                $barMap[$id] = '/bar/' . $id . ($slug ? '-' . $slug : '');
                $barNameMap[$id] = $nome;
            }
        }
        usort($posts, function($a, $b) {
            $fa = (int) ($a['is_followed'] ?? 0);
            $fb = (int) ($b['is_followed'] ?? 0);
            if ($fa !== $fb) return $fb <=> $fa;
            $ca = strtotime($a['created_at'] ?? '1970-01-01 00:00:00');
            $cb = strtotime($b['created_at'] ?? '1970-01-01 00:00:00');
            return $cb <=> $ca;
        });
        foreach ($posts as &$p) {
            $uid = (int) ($p['user_id'] ?? 0);
            $name = $userMap[$uid]['name'] ?? 'Usuário';
            $foto = $avatarMap[$uid] ?? null;
            $p['author_id'] = $uid;
            $p['author_name'] = $name;
            $p['author_avatar'] = $foto ? base_url($foto) : ("https://ui-avatars.com/api/?name=" . urlencode($name) . "&size=128&background=random");
            $p['author_profile_url'] = '/dashboard/perfil/user/' . $uid;
            $barId = (int) ($p['owner_bar_id'] ?? 0);
            $p['bar_public_url'] = $barId && isset($barMap[$barId]) ? $barMap[$barId] : null;
            $p['bar_name'] = $barId && isset($barNameMap[$barId]) ? $barNameMap[$barId] : null;
        }
        unset($p);
        return $this->response->setJSON([
            'items' => $posts,
            'count' => count($posts),
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function highlightActivePublic()
    {
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
        $link = $eventoId > 0 ? ('/evento/' . $eventoId) : null;
        $img = null;
        if (!empty($row['image_url'])) {
            $img = ltrim(str_replace('\\', '/', $row['image_url']), '/');
        } else {
            $img = !empty($row['imagem_capa']) ? ('/' . ltrim(str_replace('\\', '/', $row['imagem_capa']), '/')) : null;
        }
        return $this->response->setJSON([
            'evento_id' => $eventoId,
            'link' => $link,
            'image_url' => $img,
            'start_at' => $row['start_at'] ?? null,
            'end_at' => $row['end_at'] ?? null,
        ]);
    }

    public function postCommentsPublic($postId)
    {
        $limit  = (int) ($this->request->getGet('limit') ?? 10);
        $offset = (int) ($this->request->getGet('offset') ?? 0);
        if ($limit < 1) $limit = 10;
        if ($offset < 0) $offset = 0;
        $model = new UserPostCommentModel();
        $comments = $model->where('post_id', (int) $postId)
                          ->orderBy('created_at', 'ASC')
                          ->findAll($limit, $offset);
        return $this->response->setJSON([
            'items' => $comments,
            'count' => count($comments),
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function listaAtiva(): string
    {
        return view('lista_ativa_index');
    }

    public function listaAtivaData()
    {
        $cache = service('cache');
        $userId = (int) (session()->get('user_id') ?? 0);
        $perfil = null;
        if ($userId) {
            $perfil = (new \App\Models\User\PerfilUsuarioModel())->where('user_id', $userId)->first();
        }
        $cidadeId = (int) ($this->request->getGet('cidade_id') ?? 0);
        $lat = (float) ($this->request->getGet('lat') ?? 0);
        $lng = (float) ($this->request->getGet('lng') ?? 0);
        if ((!$lat || !$lng) && $perfil && !empty($perfil['location_lat']) && !empty($perfil['location_lng'])) {
            $lat = (float) $perfil['location_lat'];
            $lng = (float) $perfil['location_lng'];
        }
        if (!$cidadeId && $perfil && !empty($perfil['cidade_id'])) {
            $cidadeId = (int) $perfil['cidade_id'];
        }
        $radius = (int) ($this->request->getGet('radius') ?? 50);
        $page = (int) ($this->request->getGet('page') ?? 1);
        $limit = (int) ($this->request->getGet('limit') ?? 20);
        if ($page < 1) $page = 1;
        if ($limit < 1) $limit = 20;
        $offset = ($page - 1) * $limit;
        $key = 'lista_ativa_' . md5(json_encode([$lat,$lng,$cidadeId,$page,$limit,$radius]));
        try {
            $db = \Config\Database::connect();
            $items = [];
            if ($lat && $lng) {
                $sqlListas = "
                    SELECT 
                        l.lista_id,
                        l.nome,
                        l.descricao,
                        l.data_inicio,
                        l.data_fim,
                        b.bares_id,
                        b.nome AS bar_nome,
                        cid.nome AS cidade_nome,
                        (SELECT COUNT(*) FROM nl_lista_usuarios_tb u WHERE u.lista_id = l.lista_id) AS na_lista,
                        (6371 * ACOS(
                            COS(RADIANS(:lat:)) * COS(RADIANS(b.latitude)) * COS(RADIANS(b.longitude) - RADIANS(:lng:)) +
                            SIN(RADIANS(:lat:)) * SIN(RADIANS(b.latitude))
                        )) AS distancia_km
                    FROM nl_listas_tb l
                    LEFT JOIN form_perfil_bares_tb b ON b.bares_id = l.bares_id
                    LEFT JOIN base_cidades cid ON cid.id = b.cidade_id
                    WHERE l.ativo = 1
                      AND l.deleted_at IS NULL
                      AND b.latitude IS NOT NULL
                      AND b.longitude IS NOT NULL
                    HAVING distancia_km <= :radius:
                    ORDER BY distancia_km ASC, l.data_inicio DESC
                    LIMIT :limit: OFFSET :offset:
                ";
                $listas = $db->query($sqlListas, ['lat' => $lat, 'lng' => $lng, 'radius' => $radius, 'limit' => $limit, 'offset' => $offset])->getResultArray();
            } else {
                $builderLista = $db->table('nl_listas_tb l');
                $builderLista->select("
                    l.lista_id,
                    l.nome,
                    l.descricao,
                    l.data_inicio,
                    l.data_fim,
                    b.bares_id,
                    b.nome as bar_nome,
                    cid.nome as cidade_nome,
                    (SELECT COUNT(*) FROM nl_lista_usuarios_tb u WHERE u.lista_id = l.lista_id) as na_lista
                ");
                $builderLista->join('form_perfil_bares_tb b', 'b.bares_id = l.bares_id', 'left');
                $builderLista->join('base_cidades cid', 'cid.id = b.cidade_id', 'left');
                $builderLista->where('l.ativo', 1);
                $builderLista->where('l.deleted_at', null);
                if ($cidadeId) {
                    $builderLista->where('b.cidade_id', $cidadeId);
                }
                $listas = $builderLista->orderBy('l.data_inicio', 'DESC')->limit($limit, $offset)->get()->getResultArray();
            }
            foreach ($listas as $l) {
                $items[] = [
                    'tipo' => 'lista',
                    'lista_id' => (int) ($l['lista_id'] ?? 0),
                    'nome' => $l['nome'] ?? '',
                    'bares_id' => (int) ($l['bares_id'] ?? 0),
                    'bar_nome' => $l['bar_nome'] ?? '',
                    'cidade_nome' => $l['cidade_nome'] ?? '',
                    'na_lista' => (int) ($l['na_lista'] ?? 0),
                    'link_compra' => null
                ];
            }
            if ($lat && $lng) {
                $sqlEventos = "
                    SELECT
                        e.evento_id,
                        e.nome,
                        e.data_inicio,
                        e.imagem_capa,
                        b.bares_id,
                        b.nome AS bar_nome,
                        cid.nome AS cidade_nome,
                        (6371 * ACOS(
                            COS(RADIANS(:lat:)) * COS(RADIANS(b.latitude)) * COS(RADIANS(b.longitude) - RADIANS(:lng:)) +
                            SIN(RADIANS(:lat:)) * SIN(RADIANS(b.latitude))
                        )) AS distancia_km
                    FROM evt_eventos_tb e
                    LEFT JOIN form_perfil_bares_tb b ON b.bares_id = e.bares_id
                    LEFT JOIN base_cidades cid ON cid.id = b.cidade_id
                    WHERE e.deleted_at IS NULL
                    HAVING distancia_km <= :radius:
                    ORDER BY distancia_km ASC, e.data_inicio ASC
                    LIMIT :limit: OFFSET :offset:
                ";
                $eventos = $db->query($sqlEventos, ['lat' => $lat, 'lng' => $lng, 'radius' => $radius, 'limit' => $limit, 'offset' => $offset])->getResultArray();
            } else {
                $builderEvt = $db->table('evt_eventos_tb e');
                $builderEvt->select("
                    e.evento_id,
                    e.nome,
                    e.data_inicio,
                    e.imagem_capa,
                    b.bares_id,
                    b.nome as bar_nome,
                    cid.nome as cidade_nome
                ");
                $builderEvt->join('form_perfil_bares_tb b', 'b.bares_id = e.bares_id', 'left');
                $builderEvt->join('base_cidades cid', 'cid.id = b.cidade_id', 'left');
                $builderEvt->where('e.deleted_at', null);
                if ($cidadeId) {
                    $builderEvt->where('b.cidade_id', $cidadeId);
                }
                $builderEvt->orderBy('e.data_inicio', 'ASC');
                $eventos = $builderEvt->limit($limit, $offset)->get()->getResultArray();
            }
            foreach ($eventos as $e) {
                $loteRows = $db->table('evt_lotes_ingressos_tb l')
                    ->select('l.lote_id,l.nome,l.preco,l.quantidade_total,l.quantidade_vendida,l.ativo')
                    ->where('l.evento_id', (int) $e['evento_id'])
                    ->where('l.ativo', 1)
                    ->where('l.deleted_at', null)
                    ->get()->getResultArray();
                $total = 0;
                $vendidos = $db->table('evt_ingressos_vendidos_tb')->where('evento_id', (int) $e['evento_id'])->countAllResults();
                foreach ($loteRows as $lr) {
                    $total += (int) ($lr['quantidade_total'] ?? 0);
                }
                $disponivel = max(0, $total - (int) $vendidos);
                if ($total > 0) {
                    $items[] = [
                        'tipo' => 'evento',
                        'evento_id' => (int) ($e['evento_id'] ?? 0),
                        'nome' => $e['nome'] ?? '',
                        'bares_id' => (int) ($e['bares_id'] ?? 0),
                        'bar_nome' => $e['bar_nome'] ?? '',
                        'cidade_nome' => $e['cidade_nome'] ?? '',
                        'disponivel' => (int) $disponivel,
                        'vendidos' => (int) $vendidos,
                        'link_compra' => '/eventos/' . (int) ($e['evento_id'] ?? 0) . '/lotes'
                    ];
                }
            }
            $hasMore = count($listas) === $limit || count($eventos) === $limit;
            $payload = ['items' => $items, 'page' => $page, 'limit' => $limit, 'has_more' => (bool)$hasMore];
            $this->response->setJSON($payload);
            $cache?->save($key, $payload, 60);
            return $this->response;
        } catch (\Throwable $e) {
            $cached = $cache?->get($key);
            if (is_array($cached)) {
                return $this->response->setJSON($cached);
            }
            return $this->response->setJSON(['items' => [], 'page' => $page, 'limit' => $limit, 'has_more' => false]);
        }
    }

    public function listasAtivas()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('nl_listas_tb l');
        $builder->select("
            l.lista_id,
            l.nome,
            l.descricao,
            l.data_inicio,
            l.data_fim,
            l.ativo,
            b.bares_id,
            b.nome AS bar_nome,
            b.endereco AS bar_endereco,
            b.bairro_nome,
            cid.nome AS cidade_nome,
            (SELECT COUNT(*) FROM nl_lista_usuarios_tb lu WHERE lu.lista_id = l.lista_id) AS qtd_usuarios
        ");
        $builder->join('form_perfil_bares_tb b', 'b.bares_id = l.bares_id', 'left');
        $builder->join('base_cidades cid', 'cid.id = b.cidade_id', 'left');
        GeoFilter::apply($builder, 'b');
        GeoFilter::restrict($builder, 'b');
        $now = date('Y-m-d H:i:s');
        $builder->groupStart()
            ->where('l.ativo', 1)
            ->orGroupStart()
                ->where('l.data_inicio <=', $now)
                ->where('l.data_fim >=', $now)
            ->groupEnd()
        ->groupEnd();
        $builder->where('l.deleted_at', null);
        $builder->orderBy('l.data_inicio', 'DESC');
        $limit = (int) ($this->request->getGet('limit') ?? 10);
        if ($limit < 1) $limit = 10;
        $builder->limit($limit);
        $rows = $builder->get()->getResultArray();
        return $this->response->setJSON($rows);
    }

    public function baresEventos()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('form_perfil_bares_tb b');
        $builder->select("
            b.bares_id,
            b.nome,
            b.endereco,
            b.bairro_nome,
            cid.nome AS cidade_nome,
            est.nome AS estado_nome,
            t.nome AS tipo_perfil,
            b.imagem,
            (SELECT COUNT(*) FROM evt_eventos_tb e WHERE e.bares_id = b.bares_id AND e.deleted_at IS NULL AND e.data_inicio >= CURDATE()) AS eventos_proximos
        ");
        $builder->join('base_cidades cid', 'cid.id = b.cidade_id', 'left');
        $builder->join('base_estados est', 'est.id = b.estado_id', 'left');
        $builder->join('form_perfil_tipo_bar_tb t', 't.tipo_bar_id = b.tipo_bar', 'left');
        $builder->where('b.deleted_at', null);
        $tipoBarId = (int) ($this->request->getGet('tipo_bar_id') ?? 0);
        if ($tipoBarId > 0) {
            $builder->where('b.tipo_bar', $tipoBarId);
        }
        $estadoId = (int) ($this->request->getGet('estado_id') ?? 0);
        $cidadeId = (int) ($this->request->getGet('cidade_id') ?? 0);
        $bairroId = (int) ($this->request->getGet('bairro_id') ?? 0);
        if ($estadoId > 0) {
            $builder->where('b.estado_id', $estadoId);
        }
        if ($cidadeId > 0) {
            $builder->where('b.cidade_id', $cidadeId);
        }
        if ($bairroId > 0) {
            $builder->groupStart()
                ->where('b.bairro_id', $bairroId)
                ->orWhere("(b.bairro_id IS NULL AND b.bairro_nome = (SELECT nome FROM base_bairros WHERE id = {$bairroId}))", null, false)
            ->groupEnd();
        }
        $q = trim((string) ($this->request->getGet('q') ?? ''));
        if ($q !== '') {
            $esc = $db->escapeLikeString($q);
            $builder->groupStart()
                ->like('b.nome', $q)
                ->orLike('b.endereco', $q)
                ->orLike('b.nome_na_lista', $q)
                ->orLike('t.nome', $q)
                ->orLike('cid.nome', $q)
                ->orLike('est.nome', $q)
                ->orLike('b.bairro_nome', $q)
                ->orWhere("EXISTS (SELECT 1 FROM prod_produtos_tb p WHERE p.bares_id = b.bares_id AND p.deleted_at IS NULL AND p.status = 'ativo' AND (p.nome LIKE '%{$esc}%' OR p.descricao LIKE '%{$esc}%'))", null, false)
                ->orWhere("EXISTS (SELECT 1 FROM prod_cardapio_tb c INNER JOIN prod_cardapio_itens_tb ci ON ci.cardapio_id = c.cardapio_id INNER JOIN prod_produtos_tb pp ON pp.prod_id = ci.prod_id WHERE c.bares_id = b.bares_id AND c.deleted_at IS NULL AND c.status = 'ativo' AND (c.nome LIKE '%{$esc}%' OR pp.nome LIKE '%{$esc}%'))", null, false)
                ->orWhere("EXISTS (SELECT 1 FROM evt_eventos_tb e WHERE e.bares_id = b.bares_id AND e.deleted_at IS NULL AND (e.nome LIKE '%{$esc}%' OR e.descricao LIKE '%{$esc}%'))", null, false)
            ->groupEnd();
        }
        GeoFilter::apply($builder, 'b');
        if ($estadoId > 0 || $cidadeId > 0 || $bairroId > 0) {
            GeoFilter::restrict($builder, 'b');
        }
        $builder->orderBy('b.nome', 'ASC');
        $limit = (int) ($this->request->getGet('limit') ?? 6);
        if ($limit < 1) $limit = 6;
        $page = (int) ($this->request->getGet('page') ?? 1);
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;
        $builder->limit($limit, $offset);
        $rows = $builder->get()->getResultArray();
        return $this->response->setJSON($rows);
    }

    public function gastronomia()
    {
        $db = \Config\Database::connect();
        $grouped = (string) ($this->request->getGet('grouped') ?? '');
        $limit = (int) ($this->request->getGet('limit') ?? 8);
        if ($limit < 1) $limit = 8;
        if ($grouped === '1') {
            $tipos = $db->table('form_perfil_tipo_bar_tb t')
                ->select('t.tipo_bar_id, t.nome, t.categoria')
                ->where('t.ativo', 1)
                ->whereIn('t.categoria', ['Comida', 'Café'])
                ->orderBy('t.nome', 'ASC')
                ->get()->getResultArray();
            $result = [];
            foreach ($tipos as $t) {
                $bars = $db->table('form_perfil_bares_tb b')
                    ->select('b.bares_id, b.nome, b.bairro_nome, cid.nome AS cidade_nome, b.imagem, b.tipo_bar')
                    ->join('base_cidades cid', 'cid.id = b.cidade_id', 'left')
                    ->where('b.deleted_at', null)
                    ->where('b.tipo_bar', (int) $t['tipo_bar_id'])
                    ->select(\App\Libraries\GeoFilter::case('b'), false)
                    ->when((int) (($this->request->getSession()->get('geolocalizacao_personalizada')['estado_id'] ?? 0)) > 0, function($qb){
                        $estadoId = (int) ($this->request->getSession()->get('geolocalizacao_personalizada')['estado_id'] ?? 0);
                        $qb->where('b.estado_id', $estadoId);
                    })
                    ->when((int) (($this->request->getSession()->get('geolocalizacao_personalizada')['cidade_id'] ?? 0)) > 0, function($qb){
                        $cidadeId = (int) ($this->request->getSession()->get('geolocalizacao_personalizada')['cidade_id'] ?? 0);
                        $qb->where('b.cidade_id', $cidadeId);
                    })
                    ->when((int) (($this->request->getSession()->get('geolocalizacao_personalizada')['bairro_id'] ?? 0)) > 0, function($qb){
                        $bairroId = (int) ($this->request->getSession()->get('geolocalizacao_personalizada')['bairro_id'] ?? 0);
                        $qb->where('b.bairro_id', $bairroId);
                    })
                    ->orderBy('prioridade_localizacao', 'ASC')
                    ->orderBy('b.nome', 'ASC')
                    ->limit($limit)
                    ->get()->getResultArray();
                foreach ($bars as &$b) {
                    $img = $b['imagem'] ?? '';
                    if (!empty($img) && stripos($img, 'http://') !== 0 && stripos($img, 'https://') !== 0) {
                        $b['imagem'] = '/' . ltrim($img, '/');
                    }
                }
                if (count($bars) === 0) {
                    continue;
                }
                $result[] = [
                    'tipo_bar_id' => (int) $t['tipo_bar_id'],
                    'nome' => $t['nome'],
                    'categoria' => $t['categoria'],
                    'bares' => $bars,
                    'link' => '/tipos-bar/' . (int) $t['tipo_bar_id']
                ];
            }
            return $this->response->setJSON($result);
        }
        $builder = $db->table('form_perfil_bares_tb b');
        $builder->select('b.bares_id, b.nome, b.bairro_nome, cid.nome AS cidade_nome, b.imagem, b.tipo_bar');
        $builder->join('base_cidades cid', 'cid.id = b.cidade_id', 'left');
        $builder->join('form_perfil_tipo_bar_tb t', 't.tipo_bar_id = b.tipo_bar', 'left');
        $builder->where('b.deleted_at', null);
        $builder->whereIn('t.categoria', ['Comida', 'Café']);
        GeoFilter::apply($builder, 'b');
        GeoFilter::restrict($builder, 'b');
        $builder->orderBy('b.nome', 'ASC');
        $rows = $builder->limit($limit)->get()->getResultArray();
        foreach ($rows as &$b) {
            $img = $b['imagem'] ?? '';
            if (!empty($img) && stripos($img, 'http://') !== 0 && stripos($img, 'https://') !== 0) {
                $b['imagem'] = '/' . ltrim($img, '/');
            }
        }
        return $this->response->setJSON($rows);
    }

    public function tiposComEventosLista()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('form_perfil_bares_tb a');
        $builder->select('b.tipo_bar_id, b.nome, b.categoria, COUNT(DISTINCT c.evento_id) AS qtd_eventos');
        $builder->join('form_perfil_tipo_bar_tb b', 'b.tipo_bar_id = a.tipo_bar', 'inner');
        $builder->join('evt_eventos_tb c', "c.bares_id = a.bares_id AND c.deleted_at IS NULL AND c.status IN ('publicado','programado') AND c.data_inicio >= NOW()", 'inner', false);
        $builder->groupBy('b.tipo_bar_id, b.nome, b.categoria');
        $builder->orderBy('b.tipo_bar_id', 'ASC');
        $tipos = $builder->get()->getResultArray();
        return $this->response->setJSON($tipos);
    }

    public function eventosPorTipoBar()
    {
        $tipoId = (int) ($this->request->getGet('tipo_bar_id') ?? 0);
        $page = (int) ($this->request->getGet('page') ?? 1);
        $limit = (int) ($this->request->getGet('limit') ?? 8);
        if ($page < 1) $page = 1;
        if ($limit < 1) $limit = 8;
        $offset = ($page - 1) * $limit;

        $db = \Config\Database::connect();
        $builder = $db->table('evt_eventos_tb e');
        $builder->select("
            e.evento_id,
            e.nome,
            e.descricao,
            e.data_inicio,
            e.status,
            e.imagem_capa,
            e.local_customizado,
            b.nome as bar_nome,
            b.endereco as bar_endereco,
            cid.nome as cidade_nome,
            bai.nome as bairro_nome
        ");
        $builder->join('form_perfil_bares_tb b', 'b.bares_id = e.bares_id', 'left');
        $builder->join('base_cidades cid', 'cid.id = b.cidade_id', 'left');
        $builder->join('base_bairros bai', 'bai.id = b.bairro_id', 'left');
        $builder->where('e.deleted_at', null);
        $builder->whereIn('e.status', ['publicado','programado']);
        $builder->where('e.data_inicio >= NOW()', null, false);
        GeoFilter::apply($builder, 'b');
        GeoFilter::restrict($builder, 'b');
        if ($tipoId > 0) {
            $builder->where('b.tipo_bar', $tipoId);
        }
        $builder->orderBy('e.data_inicio', 'ASC');
        $builder->limit($limit, $offset);
        $events = $builder->get()->getResultArray();
        if (count($events) === 0) {
            $builder = $db->table('evt_eventos_tb e');
            $builder->select("
                e.evento_id,
                e.nome,
                e.descricao,
                e.data_inicio,
                e.status,
                e.imagem_capa,
                e.local_customizado,
                b.nome as bar_nome,
                b.endereco as bar_endereco,
                cid.nome as cidade_nome,
                bai.nome as bairro_nome
            ");
            $builder->join('form_perfil_bares_tb b', 'b.bares_id = e.bares_id', 'left');
            $builder->join('base_cidades cid', 'cid.id = b.cidade_id', 'left');
            $builder->join('base_bairros bai', 'bai.id = b.bairro_id', 'left');
            $builder->where('e.deleted_at', null);
            $builder->whereIn('e.status', ['publicado','programado']);
            $builder->where('e.data_inicio >= NOW()', null, false);
            if ($tipoId > 0) {
                $builder->where('b.tipo_bar', $tipoId);
            }
            $builder->orderBy('e.data_inicio', 'ASC');
            $builder->limit($limit, $offset);
            $events = $builder->get()->getResultArray();
        }
        foreach ($events as &$ev) {
            $img = $ev['imagem_capa'] ?? '';
            if (!empty($img) && stripos($img, 'http://') !== 0 && stripos($img, 'https://') !== 0) {
                $ev['imagem_capa'] = '/' . ltrim($img, '/');
            }
        }
        return $this->response->setJSON($events);
    }

    public function galeria()
    {
        $fotoModel = new \App\Models\Perfil\FotoBarModel();
        $rows = $fotoModel
            ->select('ft_fotos_bar_tb.foto_id, ft_fotos_bar_tb.url, b.nome AS bar_nome')
            ->join('form_perfil_bares_tb b', 'b.bares_id = ft_fotos_bar_tb.bares_id', 'left')
            ->orderBy('ft_fotos_bar_tb.created_at', 'DESC')
            ->where('ft_fotos_bar_tb.deleted_at', null)
            ->findAll((int) ($this->request->getGet('limit') ?? 8));
        return $this->response->setJSON($rows);
    }

    public function youtubeVideos()
    {
        $mediaModel = new \App\Models\User\UserMediaModel();
        $rows = $mediaModel
            ->where('deleted_at', null)
            ->where('tipo', 'youtube')
            ->orderBy('created_at', 'DESC')
            ->findAll((int) ($this->request->getGet('limit') ?? 8));
        return $this->response->setJSON($rows);
    }

    public function storiesPage(): string
    {
        return view('stories_index');
    }

    public function storiesBarList()
    {
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');
        $page = (int) ($this->request->getGet('page') ?? 1);
        $limit = (int) ($this->request->getGet('limit') ?? 12);
        if ($page < 1) $page = 1;
        if ($limit < 1) $limit = 12;
        $pull = $page * $limit;
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
            ->select(\App\Libraries\GeoFilter::case('b'), false)
            ->orderBy('prioridade_localizacao', 'ASC')
            ->orderBy('s.created_at', 'DESC')
            ->limit($pull)
            ->get()->getResultArray();
        $rows = array_merge($userRows, $barRows);
        foreach ($rows as &$s) {
            if (!empty($s['image_url'])) {
                $val = str_replace('\\', '/', (string) $s['image_url']);
                $prefix = str_replace('\\', '/', ROOTPATH . 'public' . DIRECTORY_SEPARATOR);
                if (strpos($val, $prefix) === 0) {
                    $val = substr($val, strlen($prefix));
                }
                $val = ltrim($val, '/');
                $abs = ROOTPATH . 'public' . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $val), DIRECTORY_SEPARATOR);
                if (!is_file($abs) || @filesize($abs) === 0) {
                    $s['image_url'] = null;
                } else {
                    $s['image_url'] = $val;
                }
            }
            if (empty($s['name'])) {
                $s['name'] = 'Story';
            }
        }
        unset($s);
        usort($rows, function($a, $b){
            return strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? ''));
        });
        $start = ($page - 1) * $limit;
        $items = array_slice($rows, $start, $limit);
        return $this->response->setJSON([
            'items' => $items,
            'page' => $page,
            'limit' => $limit
        ]);
    }

    public function stories()
    {
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');
        $builder = $db->table('user_stories_tb s');
        $builder->select('s.story_id, s.image_url, s.user_id, u.name as user_name');
        $builder->join('users u', 'u.id = s.user_id', 'left');
        $builder->where('s.deleted_at', null);
        $builder->where('s.expires_at >=', $now);
        $builder->orderBy('s.created_at', 'DESC');
        $limit = (int) ($this->request->getGet('limit') ?? 12);
        if ($limit < 1) $limit = 12;
        $builder->limit($limit);
        $rows = $builder->get()->getResultArray();
        foreach ($rows as &$s) {
            if (!empty($s['image_url'])) {
                $val = str_replace('\\', '/', (string) $s['image_url']);
                $prefix = str_replace('\\', '/', ROOTPATH . 'public' . DIRECTORY_SEPARATOR);
                if (strpos($val, $prefix) === 0) {
                    $val = substr($val, strlen($prefix));
                }
                $val = ltrim($val, '/');
                $abs = ROOTPATH . 'public' . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $val), DIRECTORY_SEPARATOR);
                if (!is_file($abs) || @filesize($abs) === 0) {
                    $s['image_url'] = null;
                } else {
                    $s['image_url'] = $val;
                }
            }
        }
        unset($s);
        return $this->response->setJSON($rows);
    }

    public function storiesAll()
    {
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');
        $limit = (int) ($this->request->getGet('limit') ?? 12);
        if ($limit < 1) $limit = 12;
        $userRows = $db->table('user_stories_tb s')
            ->select('s.story_id, s.image_url, s.user_id, s.created_at, u.name as name')
            ->join('users u', 'u.id = s.user_id', 'left')
            ->where('s.deleted_at', null)
            ->where('s.expires_at >=', $now)
            ->orderBy('s.created_at', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
        $barRows = $db->table('bar_stories_tb s')
            ->select('s.story_id, s.image_url, s.bares_id, s.user_id, s.created_at, b.nome as name')
            ->join('form_perfil_bares_tb b', 'b.bares_id = s.bares_id', 'left')
            ->where('s.deleted_at', null)
            ->where('s.expires_at >=', $now)
            ->orderBy('s.created_at', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
        $normalize = function (&$row) {
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
            if (empty($row['name'])) {
                $row['name'] = 'Story';
            }
        };
        foreach ($userRows as &$r) { $r['source'] = 'user'; $normalize($r); }
        unset($r);
        foreach ($barRows as &$r2) { $r2['source'] = 'bar'; $normalize($r2); }
        unset($r2);
        $all = array_merge($userRows, $barRows);
        usort($all, function($a, $b){
            return strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? ''));
        });
        if (count($all) > $limit) {
            $all = array_slice($all, 0, $limit);
        }
        return $this->response->setJSON($all);
    }

    public function albuns()
    {
        $db = \Config\Database::connect();
        $albumModel = new \App\Models\Eventos\EvtAlbumModel();
        $fotoModel = new \App\Models\Eventos\EvtAlbumFotoModel();

        $limit = (int) ($this->request->getGet('limit') ?? 8);
        if ($limit < 1) $limit = 8;
        $page = (int) ($this->request->getGet('page') ?? 1);
        if ($page < 1) $page = 1;
        $tipoEventoId = (int) ($this->request->getGet('tipo_evento_id') ?? 0);
        $need = $page * $limit;
        $geo = session('geolocalizacao_personalizada') ?? [];
        $estadoId = (int) ($geo['estado_id'] ?? 0);
        $cidadeId = (int) ($geo['cidade_id'] ?? 0);
        $bairroId = (int) ($geo['bairro_id'] ?? 0);
        $caseSql = "
            CASE
                WHEN b.estado_id = {$estadoId}
                 AND b.cidade_id = {$cidadeId}
                 AND b.bairro_id = {$bairroId} THEN 1
                WHEN b.estado_id = {$estadoId}
                 AND b.cidade_id = {$cidadeId} THEN 2
                WHEN b.estado_id = {$estadoId} THEN 3
                ELSE 4
            END AS prioridade_localizacao
        ";

        $sqlUpcoming = "
            SELECT 
                a.album_id,
                a.titulo,
                a.descricao,
                a.created_at,
                a.data_fotografia,
                a.status,
                a.thumbnail_id,
                e.evento_id,
                e.nome AS evento_nome,
                b.bares_id,
                b.nome AS bar_nome,
                b.bairro_nome,
                cid.nome AS cidade_nome,
                te.nome AS tipo_evento_nome,
                e.data_inicio,
                {$caseSql}
            FROM evt_albuns_fotos_tb a
            LEFT JOIN evt_eventos_tb e ON e.evento_id = a.evento_id
            LEFT JOIN form_perfil_bares_tb b ON b.bares_id = e.bares_id
            LEFT JOIN base_cidades cid ON cid.id = b.cidade_id
            LEFT JOIN evt_tipo_evento_tb te ON te.tipo_evento_id = e.tipo_evento_id
            WHERE a.status = 'publico'
              AND e.deleted_at IS NULL
              AND e.data_inicio >= NOW()
              AND a.created_at IS NOT NULL
              " . ($tipoEventoId > 0 ? "AND te.tipo_evento_id = {$tipoEventoId}" : "") . "
              " . ($estadoId > 0 ? "AND b.estado_id = {$estadoId}" : "") . "
              " . ($cidadeId > 0 ? "AND b.cidade_id = {$cidadeId}" : "") . "
              " . ($bairroId > 0 ? "AND b.bairro_id = {$bairroId}" : "") . "
            ORDER BY prioridade_localizacao ASC, e.data_inicio ASC
            LIMIT {$need}
        ";
        $sqlUpcoming = str_replace("WHERE a.status = 'publico'", "WHERE a.status IN ('publico','rascunho')", $sqlUpcoming);
        $upcoming = $db->query($sqlUpcoming)->getResultArray();
        $countUp = count($upcoming);
        $albums = $upcoming;
        if ($countUp < $need) {
            $remain = $need - $countUp;
            $sqlRecent = "
                SELECT 
                    a.album_id,
                    a.titulo,
                    a.descricao,
                    a.created_at,
                    a.data_fotografia,
                    a.status,
                    a.thumbnail_id,
                    e.evento_id,
                    e.nome AS evento_nome,
                    b.bares_id,
                    b.nome AS bar_nome,
                    b.bairro_nome,
                    cid.nome AS cidade_nome,
                    te.nome AS tipo_evento_nome,
                    e.data_inicio,
                    {$caseSql}
                FROM evt_albuns_fotos_tb a
                LEFT JOIN evt_eventos_tb e ON e.evento_id = a.evento_id
                LEFT JOIN form_perfil_bares_tb b ON b.bares_id = e.bares_id
                LEFT JOIN base_cidades cid ON cid.id = b.cidade_id
                LEFT JOIN evt_tipo_evento_tb te ON te.tipo_evento_id = e.tipo_evento_id
                WHERE a.status = 'publico'
                  AND (e.data_inicio IS NULL OR e.data_inicio < NOW())
                  AND a.created_at IS NOT NULL
                  " . ($tipoEventoId > 0 ? "AND te.tipo_evento_id = {$tipoEventoId}" : "") . "
                  " . ($estadoId > 0 ? "AND b.estado_id = {$estadoId}" : "") . "
                  " . ($cidadeId > 0 ? "AND b.cidade_id = {$cidadeId}" : "") . "
                  " . ($bairroId > 0 ? "AND b.bairro_id = {$bairroId}" : "") . "
                ORDER BY prioridade_localizacao ASC, a.created_at DESC
                LIMIT {$remain}
            ";
            $sqlRecent = str_replace("WHERE a.status = 'publico'", "WHERE a.status IN ('publico','rascunho')", $sqlRecent);
            $recent = $db->query($sqlRecent)->getResultArray();
            $albums = array_merge($albums, $recent);
        }
        $offset = ($page - 1) * $limit;
        if ($offset > 0 || count($albums) > $limit) {
            $albums = array_slice($albums, $offset, $limit);
        }
        
        // Garantir que sempre seja um array
        if (!is_array($albums)) {
            $albums = [];
        }

        // Buscar thumbnail para cada álbum
        foreach ($albums as &$album) {
            $thumbnailId = (int) ($album['thumbnail_id'] ?? 0);
            $thumbPath = '';
            if ($thumbnailId > 0) {
                $thumb = $fotoModel->find($thumbnailId);
                if ($thumb && !empty($thumb['nome_arquivo'])) {
                    $raw = (string) $thumb['nome_arquivo'];
                    $norm = str_replace('\\', '/', $raw);
                    $prefix = rtrim(str_replace('\\', '/', ROOTPATH . 'public'), '/');
                    if (strpos($norm, $prefix . '/') === 0) {
                        $thumbPath = substr($norm, strlen($prefix . '/'));
                    } else {
                        $thumbPath = ltrim($norm, '/');
                    }
                }
            }
            // Se não tem thumbnail, buscar primeira foto
            if (empty($thumbPath)) {
                $firstFoto = $fotoModel
                    ->where('album_id', $album['album_id'])
                    ->orderBy('ordem', 'ASC')
                    ->first();
                if ($firstFoto && !empty($firstFoto['nome_arquivo'])) {
                    $raw = (string) $firstFoto['nome_arquivo'];
                    $norm = str_replace('\\', '/', $raw);
                    $prefix = rtrim(str_replace('\\', '/', ROOTPATH . 'public'), '/');
                    if (strpos($norm, $prefix . '/') === 0) {
                        $thumbPath = substr($norm, strlen($prefix . '/'));
                    } else {
                        $thumbPath = ltrim($norm, '/');
                    }
                }
            }
            $album['thumbnail_url'] = $thumbPath;
        }
        unset($album); // Limpar referência
        
        // Garantir que sempre retorna um array JSON válido
        return $this->response
            ->setContentType('application/json')
            ->setJSON($albums);
    }

    public function albumShow($albumId)
    {
        $albumModel = new \App\Models\Eventos\EvtAlbumModel();
        $fotoModel = new \App\Models\Eventos\EvtAlbumFotoModel();
        $db = \Config\Database::connect();

        $album = $db->table('evt_albuns_fotos_tb a')
            ->select("
                a.album_id,
                a.titulo,
                a.descricao,
                a.data_fotografia,
                a.status,
                a.thumbnail_id,
                e.evento_id,
                e.nome as evento_nome,
                b.nome as bar_nome,
                b.bairro_nome,
                cid.nome as cidade_nome
            ")
            ->join('evt_eventos_tb e', 'e.evento_id = a.evento_id', 'left')
            ->join('form_perfil_bares_tb b', 'b.bares_id = e.bares_id', 'left')
            ->join('base_cidades cid', 'cid.id = b.cidade_id', 'left')
            ->where('a.album_id', (int) $albumId)
            ->get()->getRowArray();
        if (!$album) {
            return view('errors/html/error_404');
        }
        $uri = service('uri');
        $seg2 = (string) ($uri->getSegment(2) ?? '');
        $makeSlug = function($s){ $s = iconv('UTF-8','ASCII//TRANSLIT',$s); $s = strtolower($s); $s = preg_replace('/[^a-z0-9]+/i','-',$s); $s = trim($s,'-'); return $s ?: 'album'; };
        if (!preg_match('/^\d+-/', $seg2)) {
            $slug = $makeSlug($album['titulo'] ?? 'album');
            $qs = http_build_query($this->request->getGet() ?? []);
            $to = site_url('album/'.((int)$albumId).'-'.$slug) . ($qs ? ('?'.$qs) : '');
            return redirect()->to($to);
        }
        $fotos = $fotoModel->where('album_id', (int)$albumId)->orderBy('ordem','ASC')->findAll();
        return view('album', ['album' => $album, 'fotos' => $fotos]);
    }

    public function albumLike($albumId)
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        if ($userId <= 0) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'unauthorized']);
        }
        $model = new \App\Models\User\InteractionLogModel();
        $row = $model->where('actor_id', $userId)
            ->where('actor_type', 'user')
            ->where('target_type', 'album')
            ->where('target_id', (int)$albumId)
            ->where('action', 'album_like')
            ->first();
        if ($row && !empty($row['id'])) {
            $model->delete((int)$row['id']);
            return $this->response->setJSON(['liked' => false]);
        }
        $model->insert([
            'actor_id' => $userId,
            'actor_type' => 'user',
            'target_type' => 'album',
            'target_id' => (int)$albumId,
            'action' => 'album_like',
            'metadata' => '',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return $this->response->setJSON(['liked' => true]);
    }

    public function albumShare($albumId)
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        $canal = trim((string) ($this->request->getPost('canal') ?? 'link'));
        $meta = json_encode(['canal' => $canal], JSON_UNESCAPED_UNICODE);
        $model = new \App\Models\User\InteractionLogModel();
        $model->insert([
            'actor_id' => $userId ?: null,
            'actor_type' => $userId ? 'user' : 'guest',
            'target_type' => 'album',
            'target_id' => (int)$albumId,
            'action' => 'album_share',
            'metadata' => $meta,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return $this->response->setJSON(['success' => true]);
    }

    public function albumCounts($albumId)
    {
        $model = new \App\Models\User\InteractionLogModel();
        $likes = (int) $model->where('target_type', 'album')
            ->where('target_id', (int)$albumId)
            ->where('action', 'album_like')
            ->countAllResults();
        $shares = (int) $model->where('target_type', 'album')
            ->where('target_id', (int)$albumId)
            ->where('action', 'album_share')
            ->countAllResults();
        return $this->response->setJSON(['likes' => $likes, 'shares' => $shares]);
    }

    public function albunsFiltrados(): string
    {
        $db = \Config\Database::connect();
        
        // Buscar tipos de eventos para filtro
        $tiposEvento = $db->table('evt_tipo_evento_tb')
            ->select('tipo_evento_id, nome')
            ->where('ativo', 1)
            ->orderBy('nome', 'ASC')
            ->get()->getResultArray();
        
        return view('albuns', ['tipos_evento' => $tiposEvento]);
    }

    public function albunsBusca()
    {
        $db = \Config\Database::connect();
        $fotoModel = new \App\Models\Eventos\EvtAlbumFotoModel();
        
        $builder = $db->table('evt_albuns_fotos_tb a');
        $builder->select("
            a.album_id,
            a.titulo,
            a.descricao,
            a.data_fotografia,
            a.status,
            a.thumbnail_id,
            e.evento_id,
            e.nome as evento_nome,
            b.bares_id,
            b.nome as bar_nome,
            b.bairro_nome,
            cid.nome as cidade_nome,
            te.tipo_evento_id,
            te.nome as tipo_evento_nome
        ");
        $builder->join('evt_eventos_tb e', 'e.evento_id = a.evento_id', 'left');
        $builder->join('form_perfil_bares_tb b', 'b.bares_id = e.bares_id', 'left');
        $builder->join('base_cidades cid', 'cid.id = b.cidade_id', 'left');
        $builder->join('base_estados est', 'est.id = b.estado_id', 'left');
        $builder->join('evt_tipo_evento_tb te', 'te.tipo_evento_id = e.tipo_evento_id', 'left');
        $builder->whereIn('a.status', ['publico','rascunho']);
        $builder->where('e.deleted_at', null);
        $builder->where('a.created_at IS NOT NULL', null, false);
        \App\Libraries\GeoFilter::apply($builder, 'b');
        
        // Filtros
        $q = $this->request->getGet('q');
        if (!empty($q)) {
            $builder->groupStart()
                ->like('a.titulo', $q)
                ->orLike('a.descricao', $q)
                ->orLike('e.nome', $q)
                ->orLike('b.nome', $q)
                ->orLike('b.bairro_nome', $q)
                ->orLike('cid.nome', $q)
                ->orLike('est.nome', $q)
            ->groupEnd();
        }
        $tipoBarId = (int) ($this->request->getGet('tipo_bar_id') ?? 0);
        if ($tipoBarId > 0) {
            $builder->where('b.tipo_bar', $tipoBarId);
        }
        
        $barNome = $this->request->getGet('bar_nome');
        if (!empty($barNome)) {
            $builder->like('b.nome', $barNome);
        }
        
        $eventoNome = $this->request->getGet('evento_nome');
        if (!empty($eventoNome)) {
            $builder->like('e.nome', $eventoNome);
        }
        
        $tipoEventoId = $this->request->getGet('tipo_evento_id');
        if (!empty($tipoEventoId) && is_numeric($tipoEventoId)) {
            $builder->where('te.tipo_evento_id', (int) $tipoEventoId);
        }
        
        $cidade = $this->request->getGet('cidade');
        if (!empty($cidade)) {
            $builder->like('cid.nome', $cidade);
        }
        
        $bairro = $this->request->getGet('bairro');
        if (!empty($bairro)) {
            $builder->like('b.bairro_nome', $bairro);
        }
        
        $estadoId = (int) ($this->request->getGet('estado_id') ?? 0);
        $cidadeId = (int) ($this->request->getGet('cidade_id') ?? 0);
        $bairroId = (int) ($this->request->getGet('bairro_id') ?? 0);
        if ($estadoId > 0) {
            $builder->where('b.estado_id', $estadoId);
        }
        if ($cidadeId > 0) {
            $builder->where('b.cidade_id', $cidadeId);
        }
        if ($bairroId > 0) {
            $builder->groupStart()
                ->where('b.bairro_id', $bairroId)
                ->orWhere("(b.bairro_id IS NULL AND b.bairro_nome = (SELECT nome FROM base_bairros WHERE id = {$bairroId}))", null, false)
            ->groupEnd();
        }
        
        $builder->orderBy('a.created_at', 'DESC');
        
        $page = (int) ($this->request->getGet('page') ?? 1);
        $limit = (int) ($this->request->getGet('limit') ?? 24);
        if ($page < 1) $page = 1;
        if ($limit < 1) $limit = 24;
        $offset = ($page - 1) * $limit;
        $builder->limit($limit, $offset);
        
        $albums = $builder->get()->getResultArray();
        
        // Buscar thumbnail para cada álbum
        foreach ($albums as &$album) {
            $thumbnailId = (int) ($album['thumbnail_id'] ?? 0);
            $thumbPath = '';
            if ($thumbnailId > 0) {
                $thumb = $fotoModel->find($thumbnailId);
                if ($thumb && !empty($thumb['nome_arquivo'])) {
                    $raw = (string) $thumb['nome_arquivo'];
                    $norm = str_replace('\\', '/', $raw);
                    $prefix = rtrim(str_replace('\\', '/', ROOTPATH . 'public'), '/');
                    if (strpos($norm, $prefix . '/') === 0) {
                        $thumbPath = substr($norm, strlen($prefix . '/'));
                    } else {
                        $thumbPath = ltrim($norm, '/');
                    }
                }
            }
            if (empty($thumbPath)) {
                $firstFoto = $fotoModel
                    ->where('album_id', $album['album_id'])
                    ->orderBy('ordem', 'ASC')
                    ->first();
                if ($firstFoto && !empty($firstFoto['nome_arquivo'])) {
                    $raw = (string) $firstFoto['nome_arquivo'];
                    $norm = str_replace('\\', '/', $raw);
                    $prefix = rtrim(str_replace('\\', '/', ROOTPATH . 'public'), '/');
                    if (strpos($norm, $prefix . '/') === 0) {
                        $thumbPath = substr($norm, strlen($prefix . '/'));
                    } else {
                        $thumbPath = ltrim($norm, '/');
                    }
                }
            }
            $album['thumbnail_url'] = $thumbPath;
        }
        
        // Contar total (sem limit/offset)
        $builderCount = $db->table('evt_albuns_fotos_tb a');
        $builderCount->select("COUNT(DISTINCT a.album_id) as total", false);
        $builderCount->join('evt_eventos_tb e', 'e.evento_id = a.evento_id', 'left');
        $builderCount->join('form_perfil_bares_tb b', 'b.bares_id = e.bares_id', 'left');
        $builderCount->join('base_cidades cid', 'cid.id = b.cidade_id', 'left');
        $builderCount->join('base_estados est', 'est.id = b.estado_id', 'left');
        $builderCount->join('evt_tipo_evento_tb te', 'te.tipo_evento_id = e.tipo_evento_id', 'left');
        $builderCount->whereIn('a.status', ['publico','rascunho']);
        $builderCount->where('e.deleted_at', null);
        $builderCount->where('a.created_at IS NOT NULL', null, false);
        
        if (!empty($q)) {
            $builderCount->groupStart()
                ->like('a.titulo', $q)
                ->orLike('a.descricao', $q)
                ->orLike('e.nome', $q)
                ->orLike('b.nome', $q)
                ->orLike('b.bairro_nome', $q)
                ->orLike('cid.nome', $q)
                ->orLike('est.nome', $q)
            ->groupEnd();
        }
        if (!empty($barNome)) {
            $builderCount->like('b.nome', $barNome);
        }
        if (!empty($eventoNome)) {
            $builderCount->like('e.nome', $eventoNome);
        }
        if (!empty($tipoEventoId) && is_numeric($tipoEventoId)) {
            $builderCount->where('te.tipo_evento_id', (int) $tipoEventoId);
        }
        if (!empty($cidade)) {
            $builderCount->like('cid.nome', $cidade);
        }
        if (!empty($bairro)) {
            $builderCount->like('b.bairro_nome', $bairro);
        }
        if ($tipoBarId > 0) {
            $builderCount->where('b.tipo_bar', $tipoBarId);
        }
        if ($estadoId > 0) {
            $builderCount->where('b.estado_id', $estadoId);
        }
        if ($cidadeId > 0) {
            $builderCount->where('b.cidade_id', $cidadeId);
        }
        if ($bairroId > 0) {
            $builderCount->where('b.bairro_id', $bairroId);
        }
        
        $totalRow = $builderCount->get()->getRowArray();
        $total = (int) ($totalRow['total'] ?? 0);
        
        return $this->response->setJSON([
            'items' => $albums,
            'total' => (int) $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ]);
    }

    public function barsSearch()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('form_perfil_bares_tb b');
        $builder->select("
            b.bares_id,
            b.nome,
            b.endereco,
            b.bairro_nome,
            cid.nome AS cidade_nome,
            est.nome AS estado_nome,
            t.nome AS tipo_perfil,
            b.imagem
        ");
        $builder->join('base_cidades cid', 'cid.id = b.cidade_id', 'left');
        $builder->join('base_estados est', 'est.id = b.estado_id', 'left');
        $builder->join('form_perfil_tipo_bar_tb t', 't.tipo_bar_id = b.tipo_bar', 'left');
        $builder->where('b.deleted_at', null);
        $estadoId = (int) ($this->request->getGet('estado_id') ?? 0);
        $cidadeId = (int) ($this->request->getGet('cidade_id') ?? 0);
        $bairroId = (int) ($this->request->getGet('bairro_id') ?? 0);
        $tipoBarId = (int) ($this->request->getGet('tipo_bar_id') ?? 0);
        if ($tipoBarId > 0) {
            $builder->where('b.tipo_bar', $tipoBarId);
        }
        $q = trim((string) ($this->request->getGet('q') ?? ''));
        if ($q !== '') {
            $builder->groupStart()
                ->like('b.nome', $q)
                ->orLike('b.endereco', $q)
                ->orLike('t.nome', $q)
                ->orLike('cid.nome', $q)
                ->orLike('est.nome', $q)
                ->orLike('b.bairro_nome', $q)
            ->groupEnd();
        }
        GeoFilter::apply($builder, 'b');
        if ($estadoId > 0 || $cidadeId > 0 || $bairroId > 0) {
            GeoFilter::restrict($builder, 'b');
        }
        $builder->orderBy('b.nome', 'ASC');
        $limit = (int) ($this->request->getGet('limit') ?? 10);
        if ($limit < 1) $limit = 10;
        $builder->limit($limit);
        $rows = $builder->get()->getResultArray();
        return $this->response->setJSON(['items' => $rows]);
    }

    public function barPublic($barId)
    {
        $db = \Config\Database::connect();
        $bar = $db->table('form_perfil_bares_tb b')
            ->select("
                b.bares_id,
                b.nome,
                b.descricao,
                b.endereco,
                b.bairro_nome,
                cid.nome AS cidade_nome,
                b.telefone,
                b.site,
                b.tipo_bar,
                b.imagem
            ")
            ->join('base_cidades cid', 'cid.id = b.cidade_id', 'left')
            ->where('b.bares_id', (int) $barId)
            ->get()->getRowArray();
        if (!$bar) {
            return view('errors/html/error_404');
        }
        $uri = service('uri');
        $seg2 = (string) ($uri->getSegment(2) ?? '');
        $makeSlug = function($s){ $s = iconv('UTF-8','ASCII//TRANSLIT',$s); $s = strtolower($s); $s = preg_replace('/[^a-z0-9]+/i','-',$s); $s = trim($s,'-'); return $s ?: 'perfil'; };
        if (!preg_match('/^\d+-/', $seg2)) {
            $slug = $makeSlug($bar['nome'] ?? 'bar');
            $qs = http_build_query($this->request->getGet() ?? []);
            $to = site_url('bar/'.((int)$barId).'-'.$slug) . ($qs ? ('?'.$qs) : '');
            return redirect()->to($to);
        }
        $eventos = $db->table('evt_eventos_tb e')
            ->select("
                e.evento_id,
                e.nome,
                e.descricao,
                e.data_inicio,
                e.imagem_capa
            ")
            ->where('e.bares_id', (int) $barId)
            ->where('e.deleted_at', null)
            ->orderBy('e.data_inicio', 'ASC')
            ->limit(12)
            ->get()->getResultArray();
        $fotoModel = new \App\Models\Perfil\FotoBarModel();
        $fotos = $fotoModel->where('bares_id', (int) $barId)->orderBy('created_at','DESC')->findAll(12);
        $cardapioModel = new \App\Models\Produtos\CardapioModel();
        $itemModel = new \App\Models\Produtos\CardapioItemModel();
        $cardapios = $cardapioModel->where('bares_id', (int) $barId)->where('status', 'ativo')->findAll();
        $cardapioItens = [];
        foreach ($cardapios as $c) {
            $rows = $itemModel
                ->select('prod_cardapio_itens_tb.*, p.nome as produto_nome, p.unidade, p.preco as produto_preco, p.tipo_produto, p.subtipo_bebida')
                ->join('prod_produtos_tb p', 'p.prod_id = prod_cardapio_itens_tb.prod_id', 'left')
                ->where('cardapio_id', $c['cardapio_id'])
                ->orderBy('ordem', 'ASC')
                ->findAll();
            $cardapioItens[$c['cardapio_id']] = $rows;
        }
        $produtoModel = new \App\Models\Produtos\ProdutoModel();
        $produtos = $produtoModel->where('bares_id', (int) $barId)->where('status','ativo')->orderBy('created_at','DESC')->findAll(12);
        $albums = [];
        $albumModel = new \App\Models\Eventos\EvtAlbumModel();
        $albumFotoModel = new \App\Models\Eventos\EvtAlbumFotoModel();
        $albumsRows = $db->table('evt_albuns_fotos_tb a')
            ->select('a.album_id, a.titulo, a.descricao, a.data_fotografia, a.thumbnail_id, a.created_at, e.evento_id, e.nome AS evento_nome')
            ->join('evt_eventos_tb e', 'e.evento_id = a.evento_id', 'left')
            ->whereIn('a.status', ['publico','rascunho'])
            ->where('e.deleted_at', null)
            ->where('e.bares_id', (int) $barId)
            ->orderBy('a.created_at', 'DESC')
            ->limit(12)
            ->get()->getResultArray();
        foreach ($albumsRows as $al) {
            $thumbPath = '';
            $thumbId = (int) ($al['thumbnail_id'] ?? 0);
            if ($thumbId > 0) {
                $thumb = $albumFotoModel->find($thumbId);
                if ($thumb && !empty($thumb['nome_arquivo'])) {
                    $raw = (string) $thumb['nome_arquivo'];
                    $norm = str_replace('\\', '/', $raw);
                    $prefix = rtrim(str_replace('\\', '/', ROOTPATH . 'public'), '/');
                    if (strpos($norm, $prefix . '/') === 0) {
                        $thumbPath = substr($norm, strlen($prefix . '/'));
                    } else {
                        $thumbPath = ltrim($norm, '/');
                    }
                }
            }
            if (empty($thumbPath)) {
                $firstFoto = $albumFotoModel->where('album_id', (int) $al['album_id'])->orderBy('ordem','ASC')->first();
                if ($firstFoto && !empty($firstFoto['nome_arquivo'])) {
                    $raw = (string) $firstFoto['nome_arquivo'];
                    $norm = str_replace('\\', '/', $raw);
                    $prefix = rtrim(str_replace('\\', '/', ROOTPATH . 'public'), '/');
                    if (strpos($norm, $prefix . '/') === 0) {
                        $thumbPath = substr($norm, strlen($prefix . '/'));
                    } else {
                        $thumbPath = ltrim($norm, '/');
                    }
                }
            }
            $al['thumbnail_url'] = $thumbPath;
            $albums[] = $al;
        }
        $tipo = null;
        if (!empty($bar['tipo_bar'])) {
            $tipo = $db->table('form_perfil_tipo_bar_tb')->where('tipo_bar_id', (int) $bar['tipo_bar'])->get()->getRowArray();
        }
        $userId = (int) (session()->get('user_id') ?? 0);
        $userName = (string) (session()->get('user_name') ?? '');
        $userProfile = null;
        if ($userId) {
            try{
                $perfilModel = new \App\Models\User\PerfilUsuarioModel();
                $userProfile = $perfilModel->getFullProfile($userId);
            }catch(\Throwable $e){
                $userProfile = null;
            }
        }
        $generos = [];
        try {
            $generoModel = new \App\Models\User\GeneroUsuarioModel();
            $generos = $generoModel->where('ativo', 1)->orderBy('nome', 'ASC')->findAll();
        } catch (\Throwable $e) {
            $generos = [];
        }
        return view('bar_public', [
            'bar' => $bar,
            'eventos' => $eventos,
            'fotos' => $fotos,
            'cardapios' => $cardapios,
            'cardapio_itens' => $cardapioItens,
            'produtos' => $produtos,
            'albuns' => $albums,
            'tipo' => $tipo,
            'user_id' => $userId,
            'user_name' => $userName,
            'user_profile' => $userProfile,
            'generos' => $generos
        ]);
    }

    public function comprarIngresso()
    {
        $barId = (int) ($this->request->getGet('bar_id') ?? 0);
        $eventoId = (int) ($this->request->getGet('evento_id') ?? 0);
        if (!$barId || !$eventoId) {
            return redirect()->back()->with('error', 'Parâmetros inválidos');
        }
        $userId = (int) (session()->get('user_id') ?? 0);
        if (!$userId) {
            return redirect()->to('/auth/login?redirect=' . urlencode("/dashboard/perfil/create"))->with('info', 'Faça login para continuar');
        }
        return redirect()->to("/dashboard/perfil/bar/{$barId}/ingressos/manage/{$eventoId}");
    }

    public function barsRandom()
    {
        $db = \Config\Database::connect();
        $rows = $db->table('form_perfil_bares_tb b')
            ->select('b.bares_id, b.nome, b.bairro_nome, cid.nome AS cidade_nome, b.imagem')
            ->join('base_cidades cid', 'cid.id = b.cidade_id', 'left')
            ->where('b.deleted_at', null)
            ->orderBy('RAND()', '', false)
            ->limit(10)
            ->get()->getResultArray();
        foreach ($rows as &$bar) {
            $img = (string) ($bar['imagem'] ?? '');
            if (!empty($img)) {
                $norm = str_replace('\\', '/', $img);
                if (stripos($norm, 'http://') === 0 || stripos($norm, 'https://') === 0) {
                    $bar['imagem'] = $norm;
                } else {
                    $bar['imagem'] = '/' . ltrim($norm, '/');
                }
            } else {
                $bar['imagem'] = null;
            }
        }
        unset($bar);
        return $this->response->setJSON(['items' => $rows]);
    }

    public function curtirBar($barId)
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Não autenticado']);
        }
        $model = new \App\Models\User\InteractionLogModel();
        $exists = $model->where('actor_id', $userId)
            ->where('actor_type', 'user')
            ->where('target_type', 'bar')
            ->where('target_id', (int) $barId)
            ->where('action', 'like_bar')
            ->first();
        if (!$exists) {
            $model->insert([
                'actor_id' => $userId,
                'actor_type' => 'user',
                'target_type' => 'bar',
                'target_id' => (int) $barId,
                'action' => 'like_bar',
                'metadata' => null,
            ]);
        }
        $count = $model->where('target_type', 'bar')
            ->where('target_id', (int) $barId)
            ->where('action', 'like_bar')
            ->countAllResults();
        return $this->response->setJSON(['success' => true, 'likes' => (int) $count]);
    }

    public function seguirBar($barId)
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Não autenticado']);
        }
        $model = new \App\Models\User\FollowModel();
        $row = $model->where('follower_id', $userId)
            ->where('follower_type', 'user')
            ->where('target_id', (int) $barId)
            ->where('target_type', 'bar')
            ->first();
        if ($row) {
            $newStatus = (($row['status'] ?? '') === 'active') ? 'approved' : 'active';
            $model->update($row['id'], ['status' => $newStatus]);
        } else {
            $model->insert([
                'follower_id' => $userId,
                'follower_type' => 'user',
                'target_id' => (int) $barId,
                'target_type' => 'bar',
                'status' => 'active',
            ]);
        }
        $isFollowing = (bool) $model->where('follower_id', $userId)->where('follower_type','user')->where('target_id',(int)$barId)->where('target_type','bar')->where('status','active')->first();
        $count = $model->where('target_type','bar')->where('target_id',(int)$barId)->where('status','active')->countAllResults();
        return $this->response->setJSON(['success' => true, 'followers' => (int)$count, 'following' => $isFollowing]);
    }

    public function barFollowersCount($barId)
    {
        $model = new \App\Models\User\FollowModel();
        $count = $model->where('target_type','bar')->where('target_id',(int)$barId)->where('status','active')->countAllResults();
        return $this->response->setJSON(['followers' => (int)$count]);
    }

    public function barFollowingCount($barId)
    {
        $model = new \App\Models\User\FollowModel();
        $count = $model->where('follower_type','bar')->where('follower_id',(int)$barId)->where('status','active')->countAllResults();
        return $this->response->setJSON(['following' => (int)$count]);
    }

    public function barFollowersList($barId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('follows_tb f');
        $builder->select('f.follower_id, u.name as nome, p.foto_perfil as foto');
        $builder->join('users u', 'u.id = f.follower_id', 'left');
        $builder->join('perfil_usuarios_tb p', 'p.user_id = f.follower_id', 'left');
        $builder->where('f.target_type', 'bar');
        $builder->where('f.target_id', (int) $barId);
        $builder->where('f.status', 'active');
        $builder->orderBy('f.created_at', 'DESC');
        $rows = $builder->get()->getResultArray();
        $items = array_map(function($r){
            $foto = !empty($r['foto']) ? base_url($r['foto']) : ('https://ui-avatars.com/api/?name=' . urlencode($r['nome'] ?? 'Usuário') . '&size=64&background=random');
            return [
                'user_id' => (int) ($r['follower_id'] ?? 0),
                'nome' => $r['nome'] ?? 'Usuário',
                'foto' => $foto,
                'profile_url' => '/dashboard/perfil/user/' . (int) ($r['follower_id'] ?? 0),
            ];
        }, $rows);
        return $this->response->setJSON(['items' => $items, 'count' => count($items)]);
    }

    public function barIsFollowing($barId)
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        if (!$userId) {
            return $this->response->setJSON(['following' => false]);
        }
        $model = new \App\Models\User\FollowModel();
        $rel = $model->where('follower_id', $userId)
                     ->where('follower_type', 'user')
                     ->where('target_id', (int) $barId)
                     ->where('target_type', 'bar')
                     ->where('status', 'active')
                     ->first();
        return $this->response->setJSON(['following' => (bool) $rel]);
    }

    public function seguirUser($targetUserId)
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Não autenticado']);
        }
        $targetUserId = (int) $targetUserId;
        if ($targetUserId <= 0 || $targetUserId === $userId) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Requisição inválida']);
        }
        $model = new \App\Models\User\FollowModel();
        $row = $model->where('follower_id', $userId)
            ->where('follower_type', 'user')
            ->where('target_id', $targetUserId)
            ->where('target_type', 'user')
            ->first();
        if ($row) {
            $newStatus = (($row['status'] ?? '') === 'active') ? 'approved' : 'active';
            $model->update($row['id'], ['status' => $newStatus]);
        } else {
            $model->insert([
                'follower_id' => $userId,
                'follower_type' => 'user',
                'target_id' => $targetUserId,
                'target_type' => 'user',
                'status' => 'active',
            ]);
        }
        $count = $model->where('target_type','user')->where('target_id',$targetUserId)->where('status','active')->countAllResults();
        return $this->response->setJSON(['success' => true, 'followers' => (int)$count]);
    }

    public function userFollowersCount($targetUserId)
    {
        $model = new \App\Models\User\FollowModel();
        $count = $model->where('target_type','user')->where('target_id',(int)$targetUserId)->where('status','active')->countAllResults();
        return $this->response->setJSON(['followers' => (int)$count]);
    }

    public function avaliarBar($barId)
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Não autenticado']);
        }
        $rating = (int) ($this->request->getPost('rating') ?? $this->request->getGet('rating') ?? 0);
        if ($rating < 1) $rating = 1;
        if ($rating > 5) $rating = 5;
        $model = new \App\Models\User\InteractionLogModel();
        $exists = $model->where('actor_id', $userId)
            ->where('actor_type', 'user')
            ->where('target_type', 'bar')
            ->where('target_id', (int) $barId)
            ->where('action', 'rate_bar')
            ->first();
        if ($exists) {
            $model->update($exists['id'], ['metadata' => json_encode(['rating' => $rating])]);
        } else {
            $model->insert([
                'actor_id' => $userId,
                'actor_type' => 'user',
                'target_type' => 'bar',
                'target_id' => (int) $barId,
                'action' => 'rate_bar',
                'metadata' => json_encode(['rating' => $rating]),
            ]);
        }
        $rows = $model->where('target_type', 'bar')
            ->where('target_id', (int) $barId)
            ->where('action', 'rate_bar')
            ->findAll();
        $sum = 0;
        $n = 0;
        foreach ($rows as $r) {
            $meta = $r['metadata'] ?? '';
            $val = 0;
            if (!empty($meta)) {
                $dec = json_decode((string) $meta, true);
                $val = (int) ($dec['rating'] ?? 0);
            }
            if ($val > 0) {
                $sum += $val;
                $n++;
            }
        }
        $avg = $n > 0 ? round($sum / $n, 2) : 0;
        return $this->response->setJSON(['success' => true, 'avg' => $avg, 'count' => $n]);
    }

    public function buscaPage(): string
    {
        return view('busca');
    }

    public function estabelecimentosPage(): string
    {
        return view('estabelecimentos');
    }

    public function produtosPage(): string
    {
        return view('produtos_list');
    }

    public function cardapiosPage(): string
    {
        return view('cardapios_list');
    }

    public function sitemap()
    {
        $base = rtrim(site_url('/'), '/');
        $urls = [
            $base.'/',
            $base.'/eventos',
            $base.'/albuns',
            $base.'/estabelecimentos',
            $base.'/produtos',
            $base.'/cardapios',
            $base.'/busca',
        ];
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        $now = date('c');
        foreach ($urls as $u) {
            $xml .= '<url><loc>'.$u.'</loc><lastmod>'.$now.'</lastmod><changefreq>daily</changefreq><priority>0.8</priority></url>';
        }
        $xml .= '</urlset>';
        return $this->response->setHeader('Content-Type', 'application/xml')->setBody($xml);
    }
    public function produtosBusca()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('prod_produtos_tb p');
        $builder->select("
            p.prod_id,
            p.nome,
            p.preco,
            p.unidade,
            p.status,
            p.tipo_id,
            p.tipo_produto,
            tp.nome AS tipo_produto_nome,
            b.bares_id,
            b.nome AS bar_nome,
            b.bairro_nome,
            cid.nome AS cidade_nome,
            est.nome AS estado_nome
        ");
        $builder->join('form_perfil_bares_tb b', 'b.bares_id = p.bares_id', 'inner');
        $builder->join('base_cidades cid', 'cid.id = b.cidade_id', 'left');
        $builder->join('base_estados est', 'est.id = b.estado_id', 'left');
        $builder->join('prod_tipo_produtos_tb tp', 'tp.tipo_id = p.tipo_id', 'left');
        GeoFilter::apply($builder, 'b');
        GeoFilter::restrict($builder, 'b');
        $builder->where('p.deleted_at', null);
        $builder->where('p.status', 'ativo');
        $builder->where('b.deleted_at', null);
        $q = trim((string) ($this->request->getGet('q') ?? ''));
        $bairro = trim((string) ($this->request->getGet('bairro') ?? ''));
        $cidade = trim((string) ($this->request->getGet('cidade') ?? ''));
        $estado = trim((string) ($this->request->getGet('estado') ?? ''));
        $estadoId = (int) ($this->request->getGet('estado_id') ?? 0);
        $cidadeId = (int) ($this->request->getGet('cidade_id') ?? 0);
        $bairroId = (int) ($this->request->getGet('bairro_id') ?? 0);
        // filtro por tipo de produto (prod_tipo_produtos_tb)
        $tipoProdutoId = (int) ($this->request->getGet('tipo_produto_id') ?? 0);
        if ($tipoProdutoId > 0) {
            $builder->where('p.tipo_id', $tipoProdutoId);
        }
        if ($q !== '') {
            $builder->groupStart()
                ->like('p.nome', $q)
                ->orLike('p.descricao', $q)
                ->orLike('tp.nome', $q)
                ->orLike('b.nome', $q)
                ->orLike('cid.nome', $q)
                ->orLike('b.bairro_nome', $q)
            ->groupEnd();
        }
        if ($bairro !== '') {
            $builder->like('b.bairro_nome', $bairro);
        }
        if ($cidade !== '') {
            $builder->like('cid.nome', $cidade);
        }
        if ($estado !== '') {
            $builder->like('est.nome', $estado);
        }
        if ($estadoId > 0) {
            $builder->where('b.estado_id', $estadoId);
        }
        if ($cidadeId > 0) {
            $builder->where('b.cidade_id', $cidadeId);
        }
        if ($bairroId > 0) {
            $builder->groupStart()
                ->where('b.bairro_id', $bairroId)
                ->orWhere("(b.bairro_id IS NULL AND b.bairro_nome = (SELECT nome FROM base_bairros WHERE id = {$bairroId}))", null, false)
            ->groupEnd();
        }
        $limit = (int) ($this->request->getGet('limit') ?? 12);
        if ($limit < 1) $limit = 12;
        $builder->orderBy('p.created_at', 'DESC');
        $rows = $builder->limit($limit)->get()->getResultArray();
        return $this->response->setJSON($rows);
    }

    public function cardapioBusca()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('prod_cardapio_itens_tb ci');
        $builder->select("
            ci.cardapio_item_id,
            c.cardapio_id,
            c.nome AS cardapio_nome,
            c.tipo_cardapio,
            p.prod_id,
            p.nome AS nome,
            p.unidade,
            p.preco AS produto_preco,
            ci.preco_override AS preco,
            p.tipo_id,
            p.tipo_produto,
            tp.nome AS tipo_produto_nome,
            b.bares_id,
            b.nome AS bar_nome,
            b.bairro_nome,
            cid.nome AS cidade_nome,
            est.nome AS estado_nome
        ");
        $builder->join('prod_cardapio_tb c', 'c.cardapio_id = ci.cardapio_id', 'inner');
        $builder->join('prod_produtos_tb p', 'p.prod_id = ci.prod_id', 'inner');
        $builder->join('form_perfil_bares_tb b', 'b.bares_id = c.bares_id', 'inner');
        $builder->join('base_cidades cid', 'cid.id = b.cidade_id', 'left');
        $builder->join('base_estados est', 'est.id = b.estado_id', 'left');
        $builder->join('prod_tipo_produtos_tb tp', 'tp.tipo_id = p.tipo_id', 'left');
        GeoFilter::apply($builder, 'b');
        GeoFilter::restrict($builder, 'b');
        $builder->where('ci.deleted_at', null);
        $builder->where('c.deleted_at', null);
        $builder->where('p.deleted_at', null);
        $builder->where('c.status', 'ativo');
        $builder->where('b.deleted_at', null);
        $q = trim((string) ($this->request->getGet('q') ?? ''));
        $bairro = trim((string) ($this->request->getGet('bairro') ?? ''));
        $cidade = trim((string) ($this->request->getGet('cidade') ?? ''));
        $estado = trim((string) ($this->request->getGet('estado') ?? ''));
        $estadoId = (int) ($this->request->getGet('estado_id') ?? 0);
        $cidadeId = (int) ($this->request->getGet('cidade_id') ?? 0);
        $bairroId = (int) ($this->request->getGet('bairro_id') ?? 0);
        // filtro por tipo de produto (prod_tipo_produtos_tb)
        $tipoProdutoId = (int) ($this->request->getGet('tipo_produto_id') ?? 0);
        if ($tipoProdutoId > 0) {
            $builder->where('p.tipo_id', $tipoProdutoId);
        }
        if ($q !== '') {
            $builder->groupStart()
                ->like('p.nome', $q)
                ->orLike('p.descricao', $q)
                ->orLike('c.nome', $q)
                ->orLike('c.descricao', $q)
                ->orLike('c.tipo_cardapio', $q)
                ->orLike('tp.nome', $q)
                ->orLike('b.nome', $q)
                ->orLike('cid.nome', $q)
                ->orLike('b.bairro_nome', $q)
            ->groupEnd();
        }
        if ($bairro !== '') {
            $builder->like('b.bairro_nome', $bairro);
        }
        if ($cidade !== '') {
            $builder->like('cid.nome', $cidade);
        }
        if ($estado !== '') {
            $builder->like('est.nome', $estado);
        }
        if ($estadoId > 0) {
            $builder->where('b.estado_id', $estadoId);
        }
        if ($cidadeId > 0) {
            $builder->where('b.cidade_id', $cidadeId);
        }
        if ($bairroId > 0) {
            $builder->groupStart()
                ->where('b.bairro_id', $bairroId)
                ->orWhere("(b.bairro_id IS NULL AND b.bairro_nome = (SELECT nome FROM base_bairros WHERE id = {$bairroId}))", null, false)
            ->groupEnd();
        }
        $limit = (int) ($this->request->getGet('limit') ?? 12);
        if ($limit < 1) $limit = 12;
        $builder->orderBy('ci.created_at', 'DESC');
        $rows = $builder->limit($limit)->get()->getResultArray();
        foreach ($rows as &$r) {
            if (empty($r['preco'])) {
                $r['preco'] = $r['produto_preco'];
            }
        }
        unset($r);
        return $this->response->setJSON($rows);
    }
}
