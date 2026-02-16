<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BasePovoado extends Model
{
    protected $table = 'base_povoados';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['nome','cidade_id','regiao_id','bioma','status'];
}
