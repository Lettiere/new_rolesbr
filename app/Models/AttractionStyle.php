<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttractionStyle extends Model
{
    protected $table = 'evt_atracao_estilos_tb';
    protected $primaryKey = 'estilo_id';
    public $timestamps = false;

    protected $fillable = [
        'nome',
        'categoria',
        'descricao',
        'ativo',
    ];
}
