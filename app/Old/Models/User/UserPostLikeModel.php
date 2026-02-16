<?php

namespace App\Models\User;

use CodeIgniter\Model;

class UserPostLikeModel extends Model
{
    protected $table            = 'user_post_likes_tb';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['post_id', 'user_id', 'created_at'];
    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $createdField     = 'created_at';
    protected $updatedField     = '';
}
