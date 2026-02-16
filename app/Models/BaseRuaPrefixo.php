<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseRuaPrefixo extends Model
{
    protected $table = 'base_ruas_prefixos';
    protected $primaryKey = 'prefixo_id';
    public $timestamps = false;
    protected $fillable = ['nome','sigla','status'];
}
