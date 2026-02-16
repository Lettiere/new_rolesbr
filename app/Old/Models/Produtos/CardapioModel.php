<?php

namespace App\Models\Produtos;

use CodeIgniter\Model;

class CardapioModel extends Model
{
    protected $table            = 'prod_cardapio_tb';
    protected $primaryKey       = 'cardapio_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'bares_id', 'nome', 'descricao', 'tipo_cardapio', 'status'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
