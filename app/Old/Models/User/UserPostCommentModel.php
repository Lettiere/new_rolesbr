<?php

namespace App\Models\User;

use CodeIgniter\Model;

class UserPostCommentModel extends Model
{
    protected $table            = 'user_post_comments_tb';
    protected $primaryKey       = 'comment_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = ['post_id', 'user_id', 'parent_id', 'content', 'created_at', 'updated_at', 'deleted_at'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
