<?php
namespace App\Model\OrderItem;

class Model_OrderItemResource
{
    public $tableName = 'sales_order_item';
    public $primaryKey = 'entity_id';
    public $columns = [
        'entity_id',
        'order_id',
        'product_id',
        'product_name_snapshot',
        'price_snapshot',
        'quantity'
    ];
}
