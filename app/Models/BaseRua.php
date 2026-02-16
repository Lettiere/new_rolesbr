<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseRua extends Model
{
    protected $table = 'base_ruas';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['cep','prefixo_id','regiao_id','nome','cidade_id','povoado_id','bairro_id','estado_id','status'];
}
