<?php

namespace App\Models\User;

use CodeIgniter\Model;

class UserMediaModel extends Model
{
    protected $table            = 'user_media_tb';
    protected $primaryKey       = 'media_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = ['user_id', 'tipo', 'url', 'created_at', 'updated_at', 'deleted_at'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
