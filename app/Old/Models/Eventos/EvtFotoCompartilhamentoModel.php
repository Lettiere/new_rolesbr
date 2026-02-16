<?php
namespace App\Models\Eventos;
use CodeIgniter\Model;
class EvtFotoCompartilhamentoModel extends Model
{
    protected $table = 'evt_foto_compartilhamentos_tb';
    protected $primaryKey = 'id';
    protected $allowedFields = ['foto_id','user_id','canal','created_at'];
    protected $useTimestamps = false;
}
