<?php

namespace App\Models\User;

use CodeIgniter\Model;

class FollowModel extends Model
{
    protected $table            = 'follows_tb';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['follower_id', 'follower_type', 'target_id', 'target_type', 'status', 'created_at'];
    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $createdField     = 'created_at';
    protected $updatedField     = '';
}
