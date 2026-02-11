<?php
namespace App\Model\OrderAddress;

class Model_OrderAddressResource
{
    public $tableName = 'sales_order_address';
    public $primaryKey = 'entity_id';
    public $columns = [
        'entity_id',
        'order_id',
        'full_name',
        'email',
        'mobile',
        'street_address',
        'city',
        'pincode',
        'address_type'
    ];
}
