<?php

namespace App\Models\User;

use CodeIgniter\Model;

class UserStoryModel extends Model
{
    protected $table            = 'user_stories_tb';
    protected $primaryKey       = 'story_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'user_id',
        'image_url',
        'overlay_json',
        'expires_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
