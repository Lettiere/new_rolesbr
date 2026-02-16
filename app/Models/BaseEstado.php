<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseEstado extends Model
{
    protected $table = 'base_estados';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['nome','uf','codigo_ibge','pais','ddd','status'];
}
