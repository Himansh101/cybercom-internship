<?php
namespace App\Model\CartItem;

use App\Lib\Query;
use App\Database;
use PDO;

class Model_CartItemCollection
{
    public function getItems($cartId)
    {
        $resource = new Model_CartItemResource();
        $q = new Query();
        $q->select([
            'cp.quantity',
            'p.entity_id',
            'p.name',
            'p.price',
            "(SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'in_stock') as in_stock",
            "(SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'shipping_type') as shipping_type",
            'p.url_key',
            "(SELECT image_url FROM catalog_product_image WHERE product_id = p.entity_id AND is_main_image = true LIMIT 1) as image"
        ])
            ->from($resource->tableName, 'cp')
            ->join('catalog_product_entity p', 'cp.product_id = p.entity_id')
            ->where('cp.cart_id = ?', $cartId);

        $db = new Database();
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare((string) $q);
        $stmt->execute($q->getBinds());
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
