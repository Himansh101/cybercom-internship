<?php
namespace App\Model\CartItem;

class Model_CartItemResource
{
    public $tableName = 'sales_cart_product';
    public $primaryKey = 'entity_id';
    public $columns = [
        'entity_id',
        'cart_id',
        'product_id',
        'quantity',
        'added_at'
    ];
}
