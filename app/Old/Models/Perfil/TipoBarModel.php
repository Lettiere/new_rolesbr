<?php

namespace App\Models\Perfil;

use CodeIgniter\Model;

class TipoBarModel extends Model
{
    protected $table            = 'form_perfil_tipo_bar_tb';
    protected $primaryKey       = 'tipo_bar_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'nome',
        'descricao',
        'categoria',
        'ativo',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
