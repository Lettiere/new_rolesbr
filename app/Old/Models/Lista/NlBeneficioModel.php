<?php

namespace App\Models\Lista;

use CodeIgniter\Model;

class NlBeneficioModel extends Model
{
    protected $table            = 'nl_beneficios_tb';
    protected $primaryKey       = 'beneficio_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'lista_id', 'tipo', 'valor', 'descricao', 'limite_uso_total', 'limite_uso_por_usuario', 'ativo'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
