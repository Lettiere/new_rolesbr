<?php

namespace App\Models\Eventos;

use CodeIgniter\Model;

class EvtTipoEventoModel extends Model
{
    protected $table            = 'evt_tipo_evento_tb';
    protected $primaryKey       = 'tipo_evento_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'nome', 'categoria', 'descricao', 'ativo'
    ];
}
