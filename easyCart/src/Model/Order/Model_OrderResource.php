<?php
namespace App\Model\Order;

class Model_OrderResource
{
    public $tableName = 'sales_order';
    public $primaryKey = 'order_id';
    public $columns = [
        'order_id',
        'user_id',
        'order_number',
        'subtotal',
        'shipping_cost',
        'tax_amount',
        'final_amount',
        'status',
        'created_at',
        'updated_at',
        'payment_method',
        'transaction_id',
        'payment_status'
    ];
}
