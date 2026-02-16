<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventInterest extends Model
{
    protected $table = 'evt_interesse_evento_tb';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'evento_id',
        'user_id',
        'type',
    ];
}
