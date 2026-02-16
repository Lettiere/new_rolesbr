<?php

namespace App\Models\Produtos;

use CodeIgniter\Model;

class TipoProdutoModel extends Model
{
    protected $table            = 'prod_tipo_produtos_tb';
    protected $primaryKey       = 'tipo_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'familia_id', 'nome', 'descricao', 'ativo'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
