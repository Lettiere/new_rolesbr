<?php

namespace App\Models\Produtos;

use CodeIgniter\Model;

class ProdutoModel extends Model
{
    protected $table            = 'prod_produtos_tb';
    protected $primaryKey       = 'prod_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'bares_id', 'base_id', 'familia_id', 'tipo_id', 'nome', 'descricao', 'tipo_produto',
        'subtipo_bebida', 'preco', 'quantidade_estoque', 'unidade',
        'tags', 'status'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
