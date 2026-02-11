<?php
require 'src/init.php';

use App\Database;
use PDO;

try {
    $db = new Database();
    $pdo = $db->getConnection();

    // Check catalog_product_entity columns
    $stmt = $pdo->query("SELECT * FROM catalog_product_entity LIMIT 1");
    $colCount = $stmt->columnCount();
    echo "Columns in catalog_product_entity:\n";
    for ($i = 0; $i < $colCount; $i++) {
        $meta = $stmt->getColumnMeta($i);
        echo $meta['name'] . "\n";
    }

    // Check a recently inserted product
    echo "\nRecent Products:\n";
    $stmt = $pdo->query("SELECT entity_id, sku, name, url_key FROM catalog_product_entity ORDER BY entity_id DESC LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
