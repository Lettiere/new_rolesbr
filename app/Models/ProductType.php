<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{
    protected $table = 'prod_tipo_produtos_tb';
    protected $primaryKey = 'tipo_id';

    protected $fillable = [
        'familia_id',
        'nome',
        'descricao',
        'ativo',
    ];

    public function bases()
    {
        return $this->hasMany(ProductBase::class, 'tipo_id', 'tipo_id');
    }
}
