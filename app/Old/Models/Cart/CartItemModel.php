<?php

namespace App\Models\Cart;

use CodeIgniter\Model;

class CartItemModel extends Model
{
    protected $table            = 'shop_cart_items_tb';
    protected $primaryKey       = 'item_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $allowedFields    = ['cart_id', 'produto_id', 'name', 'price', 'qty'];
    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';
}
