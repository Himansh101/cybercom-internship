<?php
require 'src/init.php';

use App\Database;

try {
    $db = new App\Database();
    $pdo = $db->getConnection();

    // Postgres specific syntax to check column existence might be safer if IF NOT EXISTS fails
    // But IF NOT EXISTS usually works on modern Postgres.
    // If table exists, CREATE IF NOT EXISTS does nothing.
    // Then we try to add columns.

    $sqlCreate = "CREATE TABLE IF NOT EXISTS sales_cart_metadata (
        metadata_id SERIAL PRIMARY KEY,
        cart_id INT,
        shipping_method VARCHAR(50),
        coupon_code VARCHAR(50)
    )";
    $pdo->exec($sqlCreate);
    echo "Table verified.\n";

    // Add columns if missing
    $columns = ['cart_id' => 'INT', 'shipping_method' => 'VARCHAR(50)', 'coupon_code' => 'VARCHAR(50)'];

    foreach ($columns as $col => $type) {
        try {
            $pdo->exec("ALTER TABLE sales_cart_metadata ADD COLUMN $col $type");
            echo "Column $col added.\n";
        } catch (PDOException $e) {
            // Likely column exists
            echo "Column $col check: " . $e->getMessage() . "\n";
        }
    }

    echo "Migration complete.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
