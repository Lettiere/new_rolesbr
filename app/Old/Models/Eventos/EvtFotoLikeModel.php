<?php
namespace App\Models\Eventos;
use CodeIgniter\Model;
class EvtFotoLikeModel extends Model
{
    protected $table = 'evt_foto_likes_tb';
    protected $primaryKey = 'id';
    protected $allowedFields = ['foto_id','user_id','created_at'];
    protected $useTimestamps = false;
}
