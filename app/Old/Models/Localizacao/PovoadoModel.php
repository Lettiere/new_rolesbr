<?php

namespace App\Models\Localizacao;

use CodeIgniter\Model;

class PovoadoModel extends Model
{
    protected $table            = 'base_povoados';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['nome', 'cidade_id', 'regiao_id', 'bioma', 'status'];
}
