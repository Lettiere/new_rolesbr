<?php

namespace App\Models\Produtos;

use CodeIgniter\Model;

class FamiliaProdutoModel extends Model
{
    protected $table            = 'prod_familia_produtos_tb';
    protected $primaryKey       = 'familia_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'nome', 'descricao', 'ativo'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
