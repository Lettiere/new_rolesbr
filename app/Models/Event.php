<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Event extends Model
{
    use SoftDeletes;

    protected $table = 'evt_eventos_tb';
    protected $primaryKey = 'evento_id';

    protected $fillable = [
        'bares_id',
        'tipo_evento_id',
        'nome',
        'slug',
        'descricao',
        'local_customizado',
        'endereco_evento',
        'latitude_evento',
        'longitude_evento',
        'data_inicio',
        'data_fim',
        'hora_abertura_portas',
        'lotacao_maxima',
        'idade_minima',
        'status',
        'visibilidade',
        'imagem_capa',
        'logo_img_1',
        'logo_img_2',
        'logo_img_3',
        'logo_img_4',
        'video_youtube_url',
        'is_destaque',
        'comprovante_pagamento',
    ];

    protected $casts = [
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
        'hora_abertura_portas' => 'datetime:H:i:s',
        'is_destaque' => 'boolean',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            if (empty($model->slug) && !empty($model->nome)) {
                $model->slug = Str::slug($model->nome);
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn($model->getTable(), 'is_destaque')) {
                unset($model->is_destaque);
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn($model->getTable(), 'comprovante_pagamento')) {
                unset($model->comprovante_pagamento);
            }
        });
    }

    public function establishment()
    {
        return $this->belongsTo(Establishment::class, 'bares_id', 'bares_id');
    }

    public function type()
    {
        return $this->belongsTo(EventType::class, 'tipo_evento_id', 'tipo_evento_id');
    }
}
