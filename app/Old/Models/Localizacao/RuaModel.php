<?php

namespace App\Models\Localizacao;

use CodeIgniter\Model;

class RuaModel extends Model
{
    protected $table            = 'base_ruas';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'cep','prefixo_id','regiao_id','nome','cidade_id','povoado_id',
        'agua_encanada','iluminacao_publica','saneamento_basico','pavimentacao_id',
        'largura_inicial','largura_final','latitude_inicial','longitude_inicial',
        'latitude_final','longitude_final','status','bairro_id'
    ];
}
