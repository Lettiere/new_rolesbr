<?php

namespace App\Models\User;

use CodeIgniter\Model;

class PerfilUsuarioModel extends Model
{
    protected $table            = 'perfil_usuarios_tb';
    protected $primaryKey       = 'perfil_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id', 'cpf', 'rg', 'telefone', 'data_nascimento', 'genero_id',
        'estado_id', 'cidade_id', 'bairro_id', 'bairro_nome', 'rua_id', 'foto_perfil', 'bio',
        'location_lat', 'location_lng', 'created_at', 'updated_at'
    ];

    // Relacionamentos manuais (helpers)
    public function getFullProfile($userId)
    {
        $db = \Config\Database::connect();
        $hasBairroId = false;
        try {
            $col = $db->query("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'perfil_usuarios_tb' AND COLUMN_NAME = 'bairro_id'")->getRowArray();
            $hasBairroId = !empty($col);
        } catch (\Throwable $e) {
            $hasBairroId = false;
        }
        $select = 'perfil_usuarios_tb.*, base_estados.nome as estado_nome, base_cidades.nome as cidade_nome, base_genero_usuario.nome as genero_nome, perfil_usuarios_tb.bairro_nome as bairro_nome_text';
        if ($hasBairroId) {
            $select .= ', base_bairros.nome as bairro_nome_rel';
        } else {
            $select .= ', perfil_usuarios_tb.bairro_nome as bairro_nome_rel';
        }
        $builder = $this->select($select)
                        ->join('base_cidades', 'base_cidades.id = perfil_usuarios_tb.cidade_id', 'left')
                        ->join('base_estados', 'base_estados.id = base_cidades.estado_id', 'left')
                        ->join('base_genero_usuario', 'base_genero_usuario.genero_id = perfil_usuarios_tb.genero_id', 'left');
        if ($hasBairroId) {
            $builder = $builder->join('base_bairros', 'base_bairros.id = perfil_usuarios_tb.bairro_id', 'left');
        }
        return $builder->where('perfil_usuarios_tb.user_id', $userId)->first();
    }

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'user_id' => 'required|integer',
        'cpf'     => 'required|is_unique[perfil_usuarios_tb.cpf,perfil_id,{perfil_id}]',
        'telefone' => 'required',
        'estado_id' => 'permit_empty|is_natural',
        'cidade_id' => 'permit_empty|is_natural',
        'bairro_id' => 'permit_empty|is_natural',
        'bairro_nome' => 'permit_empty|max_length[100]',
        'rua_id' => 'permit_empty|is_natural',
        'location_lat' => 'permit_empty|decimal',
        'location_lng' => 'permit_empty|decimal',
    ];
    protected $validationMessages   = [
        'cpf' => [
            'is_unique' => 'Este CPF já está cadastrado.',
        ],
    ];
}
