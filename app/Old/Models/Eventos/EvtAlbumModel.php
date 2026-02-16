<?php
namespace App\Models\Eventos;
use CodeIgniter\Model;
class EvtAlbumModel extends Model
{
    protected $table = 'evt_albuns_fotos_tb';
    protected $primaryKey = 'album_id';
    protected $allowedFields = [
        'evento_id','fotografo_id','titulo','descricao','data_fotografia','status','thumbnail_id','created_at','updated_at'
    ];
    protected $useTimestamps = false;
}
