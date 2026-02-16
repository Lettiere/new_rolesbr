<?php

namespace App\Models\Eventos;

use CodeIgniter\Model;

class EvtLoteIngressoModel extends Model
{
    protected $table            = 'evt_lotes_ingressos_tb';
    protected $primaryKey       = 'lote_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'evento_id', 'nome', 'descricao', 'preco', 'quantidade_total', 'quantidade_vendida',
        'data_inicio_vendas', 'data_fim_vendas', 'ativo'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
