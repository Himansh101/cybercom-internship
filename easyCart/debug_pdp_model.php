<?php
require_once __DIR__ . '/src/init.php';
use App\Model\Product\Model;

// Get a product URL key or ID to test
$db = new \App\Database();
$pdo = $db->getConnection();
$stmt = $pdo->query("SELECT entity_id, url_key FROM catalog_product_entity LIMIT 1");
$prod = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prod) {
    die("No products found.");
}

echo "Testing Product: " . $prod['entity_id'] . " (" . $prod['url_key'] . ")\n";

$model = new Model();
$model->load($prod['entity_id']);
$data = $model->getData();

echo "--- DATA DUMP ---\n";
print_r($data);

// Check raw DB attribute for this product
$stmt = $pdo->prepare("SELECT * FROM catalog_product_attribute WHERE entity_id = ? AND attribute_key = 'in_stock'");
$stmt->execute([$prod['entity_id']]);
$attr = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Raw DB 'in_stock': " . var_export($attr, true) . "\n";
