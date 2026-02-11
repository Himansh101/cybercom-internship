<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/src/init.php';

// Fetch a product ID from cart
$cartId = $pdo->query("SELECT cart_id FROM sales_cart LIMIT 1")->fetchColumn();
if (!$cartId)
    die("No cart");

$stmt = $pdo->prepare("SELECT product_id FROM sales_cart_product WHERE cart_id = ? LIMIT 1");
$stmt->execute([$cartId]);
$productId = $stmt->fetchColumn();

if (!$productId)
    die("No product in cart $cartId");

echo "Testing Load for Product ID: $productId\n";

// Test Model Load
$model = new \App\Model\Product\Model();
$product = $model->load($productId);
$data = $product->getData();

echo "--- DATA ---\n";
print_r($data);

if (!$data) {
    echo "\nLOAD FAILED.\n";
    // Debug Query
    // Model::load uses $this->resource->primaryKey
    // Let's check resource
    $res = new \App\Model\Product\Resource();
    echo "Table: " . $res->tableName . "\n";
    echo "PK: " . $res->primaryKey . "\n";

    // Check raw query
    $stmt = $pdo->prepare("SELECT * FROM {$res->tableName} WHERE {$res->primaryKey} = ?");
    $stmt->execute([$productId]);
    $raw = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Raw SQL Result: ";
    print_r($raw);
} else {
    echo "\nLOAD SUCCESS.\n";
}
