<?php
require_once __DIR__ . '/src/init.php';

$db = new \App\Database();
$pdo = $db->getConnection();

echo "--- CHECK STOCK COUNT ---\n";
$stmt = $pdo->query("SELECT DISTINCT attribute_key FROM catalog_product_attribute WHERE attribute_key = 'stock_count'");
$keys = $stmt->fetchAll(PDO::FETCH_COLUMN);
if (empty($keys)) {
    echo "NO stock_count KEY FOUND.\n";
} else {
    echo "stock_count KEY EXISTS.\n";
}
