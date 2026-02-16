<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CardapioItem extends Model
{
    protected $table = 'prod_cardapio_itens_tb';
    protected $primaryKey = 'cardapio_item_id';
    public $timestamps = true;

    protected $fillable = [
        'cardapio_id',
        'prod_id',
        'categoria',
        'ordem',
        'preco_override',
        'observacoes',
    ];

    public function cardapio()
    {
        return $this->belongsTo(Cardapio::class, 'cardapio_id', 'cardapio_id');
    }
}
