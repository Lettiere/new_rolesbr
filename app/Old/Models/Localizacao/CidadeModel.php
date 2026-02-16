<?php

namespace App\Models\Localizacao;

use CodeIgniter\Model;

class CidadeModel extends Model
{
    protected $table            = 'base_cidades';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['nome', 'estado_id', 'codigo_ibge'];
}
