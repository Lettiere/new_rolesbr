<?php

namespace App\Models\Eventos;

use CodeIgniter\Model;

class EvtEventoModel extends Model
{
    protected $table            = 'evt_eventos_tb';
    protected $primaryKey       = 'evento_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'bares_id', 'tipo_evento_id', 'nome', 'slug', 'descricao', 'local_customizado',
        'data_inicio', 'data_fim', 'hora_abertura_portas', 'lotacao_maxima', 'idade_minima',
        'status', 'visibilidade', 'imagem_capa', 'video_youtube_url'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
