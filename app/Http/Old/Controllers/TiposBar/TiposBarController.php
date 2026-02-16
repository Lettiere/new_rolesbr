<?php

namespace App\Controllers\TiposBar;

use App\Controllers\BaseController;
use App\Models\Perfil\TipoBarModel;
use App\Libraries\GeoFilter;

class TiposBarController extends BaseController
{
    protected function slugify(string $nome): string
    {
        $s = iconv('UTF-8', 'ASCII//TRANSLIT', $nome);
        $s = strtolower($s);
        $s = preg_replace('/[^a-z0-9]+/i', '-', $s);
        $s = trim($s, '-');
        return $s ?: 'generico';
    }

    public function counts()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('form_perfil_tipo_bar_tb t');
        $builder->select('t.tipo_bar_id, t.nome, COUNT(e.evento_id) AS qtd');
        $builder->join('form_perfil_bares_tb b', 'b.tipo_bar = t.tipo_bar_id', 'inner');
        $builder->join('evt_eventos_tb e', 'e.bares_id = b.bares_id AND e.deleted_at IS NULL', 'inner');
        $builder->where('t.ativo', 1);
        $builder->groupBy('t.tipo_bar_id, t.nome');
        $builder->having('qtd >', 0);
        $builder->orderBy('qtd', 'DESC');
        $rows = $builder->get()->getResultArray();
        return $this->response->setJSON($rows);
    }

    public function eventosPorTipo($tipoId)
    {
        $tipoId = (int) $tipoId;
        $page = (int) ($this->request->getGet('page') ?? 1);
        $limit = (int) ($this->request->getGet('limit') ?? 12);
        if ($page < 1) $page = 1;
        if ($limit < 1) $limit = 12;
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
            bai.nome as bairro_nome,
            (SELECT COUNT(*) FROM evt_ingressos_vendidos_tb iv WHERE iv.evento_id = e.evento_id) as vendidos
        ");
        $builder->join('form_perfil_bares_tb b', 'b.bares_id = e.bares_id', 'left');
        $builder->join('base_cidades cid', 'cid.id = b.cidade_id', 'left');
        $builder->join('base_bairros bai', 'bai.id = b.bairro_id', 'left');
        GeoFilter::apply($builder, 'b');
        GeoFilter::restrict($builder, 'b');
        $builder->where('e.deleted_at', null);
        $builder->where('b.tipo_bar', $tipoId);
        $q = $this->request->getGet('q');
        if (!empty($q)) {
            $builder->groupStart()
                ->like('e.nome', $q)
                ->orLike('e.slug', $q)
                ->orLike('e.descricao', $q)
                ->orLike('b.nome', $q)
                ->orLike('b.endereco', $q)
                ->orLike('cid.nome', $q)
                ->orLike('bai.nome', $q)
            ->groupEnd();
        }
        $builder->orderBy('e.data_inicio', 'DESC');
        $builder->limit($limit, $offset);
        $events = $builder->get()->getResultArray();
        foreach ($events as &$ev) {
            $img = $ev['imagem_capa'] ?? '';
            if (!empty($img)) {
                if (stripos($img, 'http://') !== 0 && stripos($img, 'https://') !== 0) {
                    $ev['imagem_capa'] = '/' . ltrim($img, '/');
                }
            } else {
                $ev['imagem_capa'] = '';
            }
        }

        return $this->response->setJSON($events);
    }

    public function show($tipoId): string
    {
        $model = new TipoBarModel();
        $tipo = $model->find((int) $tipoId);
        if (!$tipo) {
            return view('errors/html/error_404');
        }
        $slug = $this->slugify($tipo['nome'] ?? '');
        $viewPath = 'tipos_bar/' . $slug;
        if (!is_file(APPPATH . 'Views/' . $viewPath . '.php')) {
            $viewPath = 'tipos_bar/generico';
        }
        return view($viewPath, ['tipo' => $tipo]);
    }
}
