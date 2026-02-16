<?php

namespace App\Models\Lista;

use CodeIgniter\Model;

class NlBeneficioUsoModel extends Model
{
    protected $table            = 'nl_beneficio_uso_tb';
    protected $primaryKey       = 'uso_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'beneficio_id', 'lista_usuario_id', 'referencia_tipo', 'referencia_id', 'valor_aplicado', 'data_uso'
    ];

    // Esta tabela não tem timestamps padrão, apenas data_uso
    protected $useTimestamps = false;
}
