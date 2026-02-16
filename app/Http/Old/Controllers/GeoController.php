<?php

namespace App\Controllers;

use App\Models\Localizacao\EstadoModel;
use App\Models\Localizacao\CidadeModel;
use App\Models\Localizacao\BairroModel;

class GeoController extends BaseController
{
    public function estados()
    {
        $model = new EstadoModel();
        $rows = $model->where('status', 1)->orderBy('nome', 'ASC')->findAll();
        return $this->response->setJSON($rows);
    }

    public function cidades($estadoId)
    {
        $model = new CidadeModel();
        $rows = $model->where('estado_id', (int) $estadoId)->orderBy('nome', 'ASC')->findAll();
        return $this->response->setJSON($rows);
    }

    public function bairros($cidadeId)
    {
        $model = new BairroModel();
        $rows = $model->where('cidade_id', (int) $cidadeId)->orderBy('nome', 'ASC')->findAll();
        return $this->response->setJSON($rows);
    }

    public function geolocalizacao()
    {
        $geo = session('geolocalizacao_personalizada');
        if (is_array($geo) && isset($geo['cidade'])) {
            return $this->response->setJSON($geo);
        }
        $default = [
            'localizado' => false,
            'cidade' => 'São Paulo',
            'estado' => 'São Paulo',
            'bairro' => null,
        ];
        return $this->response->setJSON($default);
    }

    public function atualizarLocalizacao()
    {
        $estadoId = (int) ($this->request->getPost('estado_id') ?? 0);
        $cidadeId = (int) ($this->request->getPost('cidade_id') ?? 0);
        $bairroId = (int) ($this->request->getPost('bairro_id') ?? 0);
        $estadoNome = (string) ($this->request->getPost('estado_nome') ?? '');
        $cidadeNome = (string) ($this->request->getPost('cidade_nome') ?? '');
        $bairroNome = (string) ($this->request->getPost('bairro_nome') ?? '');
        if ($estadoId > 0 && $estadoNome === '') {
            $eModel = new EstadoModel();
            $e = $eModel->find($estadoId);
            $estadoNome = $e['nome'] ?? '';
        }
        if ($cidadeId > 0 && $cidadeNome === '') {
            $cModel = new CidadeModel();
            $c = $cModel->find($cidadeId);
            $cidadeNome = $c['nome'] ?? '';
        }
        if ($bairroId > 0 && $bairroNome === '') {
            $bModel = new BairroModel();
            $b = $bModel->find($bairroId);
            $bairroNome = $b['nome'] ?? '';
        }
        $dados = [
            'estado_id' => $estadoId,
            'cidade_id' => $cidadeId,
            'bairro_id' => $bairroId,
            'estado' => $estadoNome,
            'cidade' => $cidadeNome,
            'bairro' => $bairroNome ?: null,
            'localizado' => true,
        ];
        session()->set('geolocalizacao_personalizada', $dados);
        return $this->response->setJSON([
            'sucesso' => true,
            'dados' => $dados,
        ]);
    }

    public function detectar()
    {
        $lat = (float) ($this->request->getPost('lat') ?? 0);
        $lng = (float) ($this->request->getPost('lng') ?? 0);
        if (!$lat || !$lng) {
            return $this->response->setJSON(['sucesso' => false, 'erro' => 'coords']);
        }
        $db = \Config\Database::connect();
        $sql = "
            SELECT 
                b.bares_id,
                b.estado_id,
                b.cidade_id,
                b.bairro_id,
                b.bairro_nome,
                cid.nome AS cidade_nome,
                est.nome AS estado_nome,
                b.latitude,
                b.longitude,
                (6371 * ACOS(
                    COS(RADIANS(:lat:)) * COS(RADIANS(b.latitude)) * COS(RADIANS(b.longitude) - RADIANS(:lng:)) +
                    SIN(RADIANS(:lat:)) * SIN(RADIANS(b.latitude))
                )) AS distancia_km
            FROM form_perfil_bares_tb b
            LEFT JOIN base_cidades cid ON cid.id = b.cidade_id
            LEFT JOIN base_estados est ON est.id = b.estado_id
            WHERE b.latitude IS NOT NULL AND b.longitude IS NOT NULL AND (b.status = 'ativo' OR b.status IS NULL)
            ORDER BY distancia_km ASC
            LIMIT 1
        ";
        $row = $db->query($sql, ['lat' => $lat, 'lng' => $lng])->getRowArray();
        if (!$row) {
            return $this->response->setJSON(['sucesso' => false]);
        }
        $dados = [
            'estado_id' => (int) ($row['estado_id'] ?? 0),
            'cidade_id' => (int) ($row['cidade_id'] ?? 0),
            'bairro_id' => (int) ($row['bairro_id'] ?? 0),
            'estado' => $row['estado_nome'] ?? '',
            'cidade' => $row['cidade_nome'] ?? '',
            'bairro' => $row['bairro_nome'] ?? null,
            'localizado' => true,
            'fonte' => 'geo'
        ];
        session()->set('geolocalizacao_personalizada', $dados);
        return $this->response->setJSON(['sucesso' => true, 'dados' => $dados]);
    }
}
