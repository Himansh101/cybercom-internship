<?php
require_once __DIR__ . '/src/init.php';

$db = new \App\Database();
$pdo = $db->getConnection();

echo "--- PRODUCT TABLE COLUMNS ---\n";
$stmt = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'catalog_product_entity'");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($cols, JSON_PRETTY_PRINT);
