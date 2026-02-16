<?php

namespace App\Models\Perfil;

use CodeIgniter\Model;

class PerfilBarClientModel extends Model
{
    protected $table            = 'form_perfil_bares_tb';
    protected $primaryKey       = 'bares_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'user_id',
        'nome',
        'endereco',
        'estado_id',
        'cidade_id',
        'bairro_id',
        'bairro_nome',
        'povoado_id',
        'prefixo_rua_id',
        'rua_id',
        'latitude',
        'longitude',
        'telefone',
        'horario_inicio',
        'horario_final',
        'tipo_bar',
        'bebidas',
        'beneficios', // JSON
        'capacidade',
        'nome_na_lista',
        'descricao',
        'site',
        'imagem',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'user_id'        => 'required|is_natural_no_zero',
        'nome'           => 'required|max_length[150]',
        'endereco'       => 'required|max_length[255]',
        'tipo_bar'       => 'required|is_natural_no_zero',
        'estado_id'      => 'permit_empty|is_natural',
        'cidade_id'      => 'permit_empty|is_natural',
        'bairro_id'      => 'permit_empty|is_natural',
        'bairro_nome'    => 'permit_empty|max_length[100]',
        'povoado_id'     => 'permit_empty|is_natural',
        'prefixo_rua_id' => 'permit_empty|is_natural',
        'rua_id'         => 'permit_empty|is_natural',
        'latitude'       => 'permit_empty|decimal',
        'longitude'      => 'permit_empty|decimal',
        'telefone'       => 'permit_empty|max_length[50]',
        'horario_inicio' => 'permit_empty|max_length[50]',
        'horario_final'  => 'permit_empty|max_length[50]',
        'capacidade'     => 'permit_empty|is_natural',
        'descricao'      => 'permit_empty',
        'status'         => 'in_list[ativo,inativo]',
    ];

    protected $validationMessages = [];

    protected $skipValidation = false;
}
