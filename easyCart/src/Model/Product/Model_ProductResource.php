<?php
namespace App\Model\Product;

class Model_ProductResource
{
    public $tableName = 'catalog_product_entity';
    public $primaryKey = 'entity_id';
    public $columns = [
        'entity_id',
        'sku',
        'name',
        'price',
        'stock_count',
        'created_at',
        'updated_at',
        'url_key'
    ];
}
