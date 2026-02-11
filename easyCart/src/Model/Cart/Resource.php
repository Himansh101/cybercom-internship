<?php
namespace App\Model\Cart;

class Resource
{
    public $tableName = 'sales_cart'; // Assuming table name based on context
    public $primaryKey = 'entity_id';
    public $columns = [
        'entity_id',
        'user_id',
        'created_at',
        'updated_at',
        'is_active'
    ];
}
