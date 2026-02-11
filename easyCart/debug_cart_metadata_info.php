<?php
require 'src/init.php';

use App\Database;
use PDO;

try {
    $db = new Database();
    $pdo = $db->getConnection();

    // Check driver
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    echo "Driver: $driver\n";

    if ($driver === 'pgsql' || $driver === 'mysql') {
        $stmt = $pdo->prepare("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'sales_cart_metadata'");
        $stmt->execute();
        $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
        print_r($cols);
    } else {
        // Fallback
        $stmt = $pdo->query("SELECT * FROM sales_cart_metadata LIMIT 1");
        $colCount = $stmt->columnCount();
        for ($i = 0; $i < $colCount; $i++) {
            $meta = $stmt->getColumnMeta($i);
            echo $meta['name'] . " (" . $meta['native_type'] . ")\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
