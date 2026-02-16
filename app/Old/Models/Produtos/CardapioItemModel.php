<?php

namespace App\Models\Produtos;

use CodeIgniter\Model;

class CardapioItemModel extends Model
{
    protected $table            = 'prod_cardapio_itens_tb';
    protected $primaryKey       = 'cardapio_item_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'cardapio_id', 'prod_id', 'categoria', 'ordem', 'preco_override', 'observacoes'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
