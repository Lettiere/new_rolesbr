<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cardapio extends Model
{
    protected $table = 'prod_cardapio_tb';
    protected $primaryKey = 'cardapio_id';

    protected $fillable = [
        'bares_id',
        'nome',
        'descricao',
        'tipo_cardapio',
        'status',
    ];

    public function items()
    {
        return $this->hasMany(CardapioItem::class, 'cardapio_id', 'cardapio_id');
    }
}
