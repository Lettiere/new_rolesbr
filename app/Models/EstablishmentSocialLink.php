<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstablishmentSocialLink extends Model
{
    protected $table = 'establishment_social_links';
    protected $primaryKey = 'id';

    protected $fillable = [
        'bares_id',
        'network',
        'handle',
    ];

    public function establishment()
    {
        return $this->belongsTo(Establishment::class, 'bares_id', 'bares_id');
    }
}

