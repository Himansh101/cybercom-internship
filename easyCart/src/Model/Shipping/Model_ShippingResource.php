<?php
namespace App\Model\Shipping;

class Model_ShippingResource
{
    public $tableName = 'sales_shipping_method';
    public $primaryKey = 'entity_id';
    public $columns = [
        'entity_id',
        'code',
        'name',
        'type',
        'base_cost',
        'rate_percent',
        'limit_amount',
        'is_active',
        'sort_order'
    ];
}
