<?php

namespace App\Models\Eventos;

use CodeIgniter\Model;

class EvtAtracaoModel extends Model
{
    protected $table            = 'evt_atracoes_tb';
    protected $primaryKey       = 'atracao_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'nome', 'tipo', 'estilo', 'descricao', 'rede_social'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
