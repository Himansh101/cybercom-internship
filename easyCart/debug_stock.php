<?php
require_once __DIR__ . '/src/init.php';
echo "Categories:\n";
$stmt = $pdo->query("SELECT * FROM catalog_category_entity");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\nProducts with low stock:\n";
$stmt = $pdo->query("SELECT p.entity_id, p.name, p.stock_count, 
    (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'in_stock') as attr_in_stock
    FROM catalog_product_entity p 
    WHERE p.stock_count <= 2");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
