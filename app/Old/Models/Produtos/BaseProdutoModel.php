<?php

namespace App\Models\Produtos;

use CodeIgniter\Model;

class BaseProdutoModel extends Model
{
    protected $table            = 'prod_base_produto_tb';
    protected $primaryKey       = 'base_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'tipo_id', 'nome', 'descricao', 'caracteristica', 'unidade_padrao', 'tags', 'ativo'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
