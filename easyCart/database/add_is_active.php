<?php
require_once __DIR__ . '/../src/config/database.php';

$db = new Database();
$conn = $db->getConnection();

if (!$conn) {
    die("Connection failed!");
}

echo "Adding is_active column to customer_entity if not exists...\n";
try {
    $checkColumn = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name='customer_entity' AND column_name='is_active'");
    if (!$checkColumn->fetch()) {
        $conn->exec("ALTER TABLE customer_entity ADD COLUMN is_active BOOLEAN DEFAULT TRUE");
        echo "Column is_active added.\n";
    } else {
        echo "Column is_active already exists.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
