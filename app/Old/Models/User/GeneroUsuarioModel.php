<?php

namespace App\Models\User;

use CodeIgniter\Model;

class GeneroUsuarioModel extends Model
{
    protected $table            = 'base_genero_usuario';
    protected $primaryKey       = 'genero_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['nome', 'ativo', 'created_at', 'updated_at'];
    protected $useTimestamps    = true;
}
