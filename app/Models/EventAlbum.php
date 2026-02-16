<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventAlbum extends Model
{
    protected $table = 'evt_albuns_fotos_tb';
    protected $primaryKey = 'album_id';
    public $timestamps = false;

    protected $fillable = [
        'evento_id',
        'fotografo_id',
        'titulo',
        'descricao',
        'data_fotografia',
        'status',
        'thumbnail_id',
        'created_at',
        'updated_at',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'evento_id', 'evento_id');
    }

    public function photos()
    {
        return $this->hasMany(EventAlbumPhoto::class, 'album_id', 'album_id')->orderBy('ordem','ASC');
    }
}

