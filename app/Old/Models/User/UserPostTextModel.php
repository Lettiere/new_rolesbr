<?php

namespace App\Models\User;

use CodeIgniter\Model;

class UserPostTextModel extends Model
{
    protected $table            = 'user_post_texts_tb';
    protected $primaryKey       = 'text_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = ['post_id', 'text_body', 'created_at', 'updated_at', 'deleted_at'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
