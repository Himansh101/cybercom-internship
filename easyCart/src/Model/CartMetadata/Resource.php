<?php
namespace App\Model\CartMetadata;

class Resource
{
    public $tableName = 'sales_cart_metadata';
    public $primaryKey = 'metadata_id';
    public $columns = [
        'metadata_id',
        'cart_id',
        'shipping_method',
        'coupon_code'
    ];
}
