<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileUser extends Model
{
    protected $table = 'perfil_usuarios_tb';
    protected $primaryKey = 'perfil_id';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'cpf',
        'rg',
        'telefone',
        'data_nascimento',
        'genero_id',
        'estado_id',
        'cidade_id',
        'bairro_id',
        'bairro_nome',
        'rua_id',
        'foto_perfil',
        'bio',
        'location_lat',
        'location_lng',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

