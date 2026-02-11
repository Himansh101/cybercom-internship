<?php
namespace App\Model\Customer;

class Resource
{
    public $tableName = 'customer_entity';
    public $primaryKey = 'entity_id';
    public $columns = [
        'entity_id',
        'name',
        'email',
        'mobile',
        'password',
        'created_at',
        'updated_at',
        'is_active',
        'is_admin',
        'street_address',
        'city',
        'pincode'
    ];
}
