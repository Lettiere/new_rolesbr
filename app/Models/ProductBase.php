<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBase extends Model
{
    protected $table = 'prod_base_produto_tb';
    protected $primaryKey = 'base_id';

    protected $fillable = [
        'tipo_id',
        'nome',
        'descricao',
        'caracteristica',
        'unidade_padrao',
        'tags',
        'ativo',
    ];

    public function type()
    {
        return $this->belongsTo(ProductType::class, 'tipo_id', 'tipo_id');
    }
}
