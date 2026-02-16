<?php

namespace App\Models\Produtos;

use CodeIgniter\Model;

class FotoProdutoModel extends Model
{
    protected $table            = 'fto_produtos_tb';
    protected $primaryKey       = 'foto_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'prod_id', 'url', 'descricao', 'ordem'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
