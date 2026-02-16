<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventType extends Model
{
    protected $table = 'evt_tipo_evento_tb';
    protected $primaryKey = 'tipo_evento_id';
    public $timestamps = false;

    protected $fillable = [
        'nome',
        'categoria',
        'descricao',
        'ativo',
    ];
}
