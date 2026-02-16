<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstablishmentType extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'form_perfil_tipo_bar_tb';
    protected $primaryKey = 'tipo_bar_id';

    protected $fillable = [
        'nome',
        'descricao',
        'categoria',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];
}
