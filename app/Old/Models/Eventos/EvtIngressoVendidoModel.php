<?php

namespace App\Models\Eventos;

use CodeIgniter\Model;

class EvtIngressoVendidoModel extends Model
{
    protected $table            = 'evt_ingressos_vendidos_tb';
    protected $primaryKey       = 'ingresso_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'evento_id',
        'lote_id',
        'user_id',
        'nome_comprador',
        'email_comprador',
        'codigo_unico',
        'status',
        'valor_pago',
        'data_compra',
        'data_utilizacao',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
