<?php
namespace App\Model\CartMetadata;

class Model_CartMetadataResource
{
    public $tableName = 'sales_cart_metadata';
    public $primaryKey = 'entity_id';
    public $columns = [
        'entity_id',
        'cart_id',
        'shipping_method',
        'coupon_code',
        'discount_amount'
    ];
}
