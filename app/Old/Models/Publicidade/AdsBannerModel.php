<?php

namespace App\Models\Publicidade;

use CodeIgniter\Model;

class AdsBannerModel extends Model
{
    protected $table            = 'ads_banners_tb';
    protected $primaryKey       = 'banner_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields = [
        'title',
        'link_url',
        'placement',
        'active',
        'start_at',
        'end_at',
        'image_desktop_url',
        'image_mobile_url',
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
