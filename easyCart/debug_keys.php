<?php
require_once __DIR__ . '/src/init.php';

$db = new \App\Database();
$pdo = $db->getConnection();

echo "--- DISTINCT KEYS ---\n";
$stmt = $pdo->query("SELECT DISTINCT attribute_key FROM catalog_product_attribute");
$keys = $stmt->fetchAll(PDO::FETCH_COLUMN);
print_r($keys);

echo "\n--- ATTRIBUTES FOR PRODUCT ID 1 ---\n";
// Find a valid product ID first
$pid = $pdo->query("SELECT entity_id FROM catalog_product_attribute LIMIT 1")->fetchColumn();
if ($pid) {
    echo "Checking Product ID: $pid\n";
    $stmt = $pdo->prepare("SELECT * FROM catalog_product_attribute WHERE entity_id = ?");
    $stmt->execute([$pid]);
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
}
