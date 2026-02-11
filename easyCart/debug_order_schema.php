<?php
require 'src/init.php';

use App\Database;

try {
    $db = new Database();
    $pdo = $db->getConnection();

    // Attempt to fetch one row from sales_order to see columns
    $stmt = $pdo->query("SELECT * FROM sales_order LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<pre>";
    if ($row) {
        print_r(array_keys($row));
    } else {
        // If empty, try to get column meta
        echo "Table seems empty, trying to finding columns via information_schema or just showing valid query execution.\n";
        $colCount = $stmt->columnCount();
        for ($i = 0; $i < $colCount; $i++) {
            $meta = $stmt->getColumnMeta($i);
            echo $meta['name'] . "\n";
        }
    }
    echo "</pre>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
