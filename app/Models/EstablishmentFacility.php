<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstablishmentFacility extends Model
{
    protected $table = 'establishment_facilities';
    protected $primaryKey = 'id';

    protected $fillable = [
        'slug',
        'nome',
        'descricao',
        'ativo',
        'ordem',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function establishments()
    {
        return $this->belongsToMany(
            Establishment::class,
            'establishment_facility_pivot',
            'facility_id',
            'bares_id'
        );
    }
}

