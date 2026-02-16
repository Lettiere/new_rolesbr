<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductFamily extends Model
{
    protected $table = 'prod_familia_produtos_tb';
    protected $primaryKey = 'familia_id';

    protected $fillable = [
        'nome',
        'descricao',
        'ativo',
    ];
}
