<?php

namespace App\Models\Localizacao;

use CodeIgniter\Model;

class RuaPrefixoModel extends Model
{
    protected $table            = 'base_ruas_prefixos';
    protected $primaryKey       = 'prefixo_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['nome', 'sigla', 'status'];
}
