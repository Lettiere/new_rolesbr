<?php
namespace App\Models\Eventos;
use CodeIgniter\Model;
class EvtAlbumFotoModel extends Model
{
    protected $table = 'evt_album_fotos_tb';
    protected $primaryKey = 'foto_id';
    protected $allowedFields = [
        'album_id','nome_arquivo','titulo','descricao','ordem','eh_thumbnail','created_at'
    ];
    protected $useTimestamps = false;
}
