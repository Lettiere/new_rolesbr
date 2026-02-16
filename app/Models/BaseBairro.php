<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseBairro extends Model
{
    protected $table = 'base_bairros';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['nome','cidade_id','status'];
}
