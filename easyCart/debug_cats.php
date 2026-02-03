<?php
require_once __DIR__ . '/src/init.php';
echo "Categories:\n";
$stmt = $pdo->query("SELECT * FROM catalog_category_entity");
$cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($cats as $cat) {
    echo "- ID: {$cat['entity_id']}, Name: {$cat['name']}\n";
}
