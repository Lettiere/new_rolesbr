<?php

namespace App\Models\Localizacao;

use CodeIgniter\Model;

class BairroModel extends Model
{
    protected $table            = 'base_bairros';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['nome', 'cidade_id', 'status'];
}
