<?php

namespace App\Models\User;

use CodeIgniter\Model;

class UserPostModel extends Model
{
    protected $table            = 'user_posts_tb';
    protected $primaryKey       = 'post_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = [
        'user_id',
        'owner_user_id',
        'owner_bar_id',
        'posted_as_type',
        'posted_as_id',
        'visibility',
        'caption',
        'image_url',
        'location_lat',
        'location_lng',
        'neighborhood',
        'location_id',
        'likes_count',
        'comments_count',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
