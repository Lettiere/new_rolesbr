<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Establishment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'form_perfil_bares_tb';
    protected $primaryKey = 'bares_id';

    protected $fillable = [
        'user_id',
        'nome',
        'endereco',
        'telefone',
        'horario_inicio',
        'horario_final',
        'tipo_bar',
        'estado_id',
        'cidade_id',
        'bairro_id',
        'bairro_nome',
        'povoado_id',
        'prefixo_rua_id',
        'rua_id',
        'latitude',
        'longitude',
        'bebidas',
        'beneficios',
        'capacidade',
        'nome_na_lista',
        'descricao',
        'site',
        'imagem',
        'status',
    ];

    protected $casts = [
        'nome_na_lista' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function type()
    {
        return $this->belongsTo(EstablishmentType::class, 'tipo_bar', 'tipo_bar_id');
    }

    public function socialLinks()
    {
        return $this->hasMany(EstablishmentSocialLink::class, 'bares_id', 'bares_id');
    }

    public function facilities()
    {
        return $this->belongsToMany(
            EstablishmentFacility::class,
            'establishment_facility_pivot',
            'bares_id',
            'facility_id'
        );
    }
}
