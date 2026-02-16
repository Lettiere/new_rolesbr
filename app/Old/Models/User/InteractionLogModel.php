<?php

namespace App\Models\User;

use CodeIgniter\Model;

class InteractionLogModel extends Model
{
    protected $table            = 'interaction_log_tb';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['actor_id', 'actor_type', 'target_type', 'target_id', 'action', 'metadata', 'created_at'];
    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $createdField     = 'created_at';
    protected $updatedField     = '';
}
