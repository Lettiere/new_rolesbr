<?php


namespace App\Models\Lista;

use CodeIgniter\Model;

class NlListaModel extends Model
{
    protected $table            = 'nl_listas_tb';
    protected $primaryKey       = 'lista_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'bares_id', 'nome', 'descricao', 'tipo', 'ativo', 'data_inicio', 'data_fim'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
