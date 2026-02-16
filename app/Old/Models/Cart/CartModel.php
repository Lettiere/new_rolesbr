<?php

namespace App\Models\Cart;

use CodeIgniter\Model;

class CartModel extends Model
{
    protected $table            = 'shop_carts_tb';
    protected $primaryKey       = 'cart_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = ['session_id', 'user_id', 'bares_id', 'status'];
    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';
}
