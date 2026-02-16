<?php

namespace App\Models\Perfil;

use CodeIgniter\Model;

class BarStoryModel extends Model
{
    protected $table            = 'bar_stories_tb';
    protected $primaryKey       = 'story_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'bares_id',
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

