<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseCidade extends Model
{
    protected $table = 'base_cidades';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['nome','estado_id','codigo_ibge'];
}
