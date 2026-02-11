<?php
namespace App\Model\Cart;

class Model_CartResource
{
    public $tableName = 'sales_cart';
    public $primaryKey = 'entity_id';
    public $columns = [
        'entity_id',
        'user_id',
        'created_at',
        'updated_at',
        'is_active'
    ];
}
