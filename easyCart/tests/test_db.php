<?php
require_once __DIR__ . '/../src/config/database.php';
$db = new Database();
$conn = $db->getConnection();
if ($conn) {
    echo "Connection successful!\n";

    $tablesToCheck = ['catalog_product_entity', 'catalog_product_attribute', 'catalog_category_entity'];
    foreach ($tablesToCheck as $table) {
        echo "Columns for $table:\n";
        $stmt = $conn->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = '$table'");
        $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $col) {
            echo " - " . $col['column_name'] . " (" . $col['data_type'] . ")\n";
        }
    }
} else {
    echo "Connection failed.\n";
}
