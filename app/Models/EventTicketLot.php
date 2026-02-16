<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventTicketLot extends Model
{
    protected $table = 'evt_lotes_ingressos_tb';
    protected $primaryKey = 'lote_id';
    public $timestamps = true;

    protected $fillable = [
        'evento_id',
        'nome',
        'tipo',
        'preco',
        'quantidade_total',
        'quantidade_vendida',
        'data_inicio_vendas',
        'data_fim_vendas',
        'ativo',
        'status',
    ];
}
