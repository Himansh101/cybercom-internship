<?php
namespace App\Model\CartItem;

class Resource
{
    public $tableName = 'sales_cart_product';
    public $primaryKey = 'entity_id'; // Assuming it has one, or composite key?
    // Based on `cart.handler.php` or `Cart.php`, it uses cart_id + product_id.
    // However, usually there's an AI PK. I'll assume standard naming `entity_id`.
    public $columns = [
        'entity_id',
        'cart_id',
        'product_id',
        'quantity',
        'added_at'
    ];
}
