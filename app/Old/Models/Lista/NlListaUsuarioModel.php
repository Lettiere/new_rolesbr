<?php

namespace App\Models\Lista;

use CodeIgniter\Model;

class NlListaUsuarioModel extends Model
{
    protected $table            = 'nl_lista_usuarios_tb';
    protected $primaryKey       = 'lista_usuario_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'lista_id', 'user_id', 'codigo_convite', 'status', 'origem'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
