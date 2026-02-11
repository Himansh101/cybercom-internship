<?php
require_once __DIR__ . '/src/init.php';

use App\Model\Product\Collection;

// Init DB to bypass collection if needed
$db = new \App\Database();
$pdo = $db->getConnection();

echo "--- DIRECT ATTRIBUTE CHECK ---\n";
// Check attributes for one product ID = 1 (or any existing ID)
$stmt = $pdo->query("SELECT entity_id, sku, name FROM catalog_product_entity LIMIT 1");
$prod = $stmt->fetch(PDO::FETCH_ASSOC);
if ($prod) {
    echo "Product ID: " . $prod['entity_id'] . "\n";
    $stmt = $pdo->prepare("SELECT * FROM catalog_product_attribute WHERE entity_id = ?");
    $stmt->execute([$prod['entity_id']]);
    $attrs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($attrs as $a) {
        echo "{$a['attribute_key']}: {$a['attribute_value']}\n";
    }
} else {
    echo "No products found.\n";
}

echo "\n--- COLLECTION OUTPUT Check ---\n";
$collection = new Collection();
$collection->setPage(1, 5);
$items = $collection->getItems();

foreach ($items as $item) {
    echo "ID: {$item['entity_id']} | Name: {$item['name']} | InStock(Processed): " . ($item['in_stock'] ? 'YES' : 'NO') . " | StockCount(Raw): '{$item['stock_count']}' | InStockAttr(Raw): '{$item['in_stock']}'\n";
    // wait, getItems overwrites in_stock key. 
    // I need to see the raw value. 
    // I'll modify Collection temporarily or just assume the join worked if stock_count is present.
}
