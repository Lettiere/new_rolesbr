<?php

namespace App\Models\Eventos;

use CodeIgniter\Model;

class EvtEventoAtracaoModel extends Model
{
    protected $table            = 'evt_evento_atracoes_tb';
    protected $primaryKey       = 'evento_id'; // Atenção: Chave composta, mas CodeIgniter requer uma primária. Usando evento_id para compatibilidade básica
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'evento_id', 'atracao_id', 'ordem', 'horario_previsto'
    ];
}
