<?php

namespace App\Models\Perfil;

use CodeIgniter\Model;

class FotoBarModel extends Model
{
    protected $table            = 'ft_fotos_bar_tb';
    protected $primaryKey       = 'foto_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'bares_id',
        'url',
        'descricao',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'bares_id' => 'required|is_natural_no_zero',
        'url'      => 'required|max_length[255]',
    ];
}
