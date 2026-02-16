<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $table = 'prod_produtos_tb';
    protected $primaryKey = 'prod_id';

    protected $fillable = [
        'bares_id',
        'base_id',
        'familia_id',
        'tipo_id',
        'nome',
        'descricao',
        'tipo_produto',
        'subtipo_bebida',
        'preco',
        'quantidade_estoque',
        'unidade',
        'tags',
        'status',
    ];

    protected $casts = [
        'preco' => 'decimal:2',
        'quantidade_estoque' => 'integer',
    ];

    public function establishment()
    {
        return $this->belongsTo(Establishment::class, 'bares_id', 'bares_id');
    }

    public function base()
    {
        return $this->belongsTo(ProductBase::class, 'base_id', 'base_id');
    }

    public function family()
    {
        return $this->belongsTo(ProductFamily::class, 'familia_id', 'familia_id');
    }

    public function type()
    {
        return $this->belongsTo(ProductType::class, 'tipo_id', 'tipo_id');
    }
}
