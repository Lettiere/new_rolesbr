<?php
namespace App\Models\Eventos;
use CodeIgniter\Model;
class EvtFotoComentarioModel extends Model
{
    protected $table = 'evt_foto_comentarios_tb';
    protected $primaryKey = 'comentario_id';
    protected $allowedFields = ['foto_id','user_id','comentario','status','created_at','updated_at'];
    protected $useTimestamps = false;
}
