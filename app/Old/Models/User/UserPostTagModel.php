<?php

namespace App\Models\User;

use CodeIgniter\Model;

class UserPostTagModel extends Model
{
    protected $table            = 'user_post_tags_tb';
    protected $primaryKey       = 'tag_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['post_id', 'target_type', 'target_id', 'created_at'];
    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $createdField     = 'created_at';
}
