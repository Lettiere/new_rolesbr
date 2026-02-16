<?php

namespace App\Models\Eventos;

use CodeIgniter\Model;

class SponsoredHighlightModel extends Model
{
    protected $table = 'sponsored_highlights_tb';
    protected $primaryKey = 'highlight_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = ['evento_id', 'image_url', 'start_at', 'end_at', 'active', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
