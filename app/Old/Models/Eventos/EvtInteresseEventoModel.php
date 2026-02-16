<?php

namespace App\Models\Eventos;

use CodeIgniter\Model;

class EvtInteresseEventoModel extends Model
{
    protected $table            = 'evt_interesse_evento_tb';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['evento_id', 'user_id', 'created_at', 'updated_at'];
    protected $useTimestamps    = true;
}
