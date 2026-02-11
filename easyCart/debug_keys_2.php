<?php
require_once __DIR__ . '/src/init.php';

$db = new \App\Database();
$pdo = $db->getConnection();

echo "--- STOCK KEYS ---\n";
$stmt = $pdo->query("SELECT DISTINCT attribute_key FROM catalog_product_attribute WHERE attribute_key LIKE '%stock%'");
$keys = $stmt->fetchAll(PDO::FETCH_COLUMN);
print_r($keys);

echo "\n--- ALL KEYS (First 20) ---\n";
$stmt = $pdo->query("SELECT DISTINCT attribute_key FROM catalog_product_attribute LIMIT 20");
$keys = $stmt->fetchAll(PDO::FETCH_COLUMN);
print_r($keys);
