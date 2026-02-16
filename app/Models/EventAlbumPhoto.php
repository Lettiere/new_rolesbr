<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventAlbumPhoto extends Model
{
    protected $table = 'evt_album_fotos_tb';
    protected $primaryKey = 'foto_id';
    public $timestamps = false;

    protected $fillable = [
        'album_id',
        'nome_arquivo',
        'titulo',
        'descricao',
        'ordem',
        'eh_thumbnail',
        'created_at',
    ];

    public function album()
    {
        return $this->belongsTo(EventAlbum::class, 'album_id', 'album_id');
    }
}

