<?php
require 'src/init.php';

use App\Database;

try {
    $db = new Database();
    $pdo = $db->getConnection();

    // Check sales_cart columns
    $stmt = $pdo->query("SELECT * FROM sales_cart LIMIT 1");
    $colCount = $stmt->columnCount();
    echo "Columns in sales_cart:\n";
    for ($i = 0; $i < $colCount; $i++) {
        $meta = $stmt->getColumnMeta($i);
        echo $meta['name'] . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
