<?php
require_once __DIR__ . '/../config/database.php';
$db = new Database();
$conn = $db->getConnection();
if ($conn) {
    echo "Checking triggers...\n";
    $stmt = $conn->query("SELECT tgname, tgnargs, tgtype FROM pg_trigger JOIN pg_class ON pg_class.oid = tgrelid WHERE relname IN ('catalog_category_entity', 'catalog_product_entity', 'catalog_product_attribute')");
    $triggers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($triggers as $t) {
        echo " - " . $t['tgname'] . "\n";
    }
    
    echo "Checking constraints...\n";
    $stmt = $conn->query("SELECT conname, contype, pg_get_constraintdef(oid) as consrc FROM pg_constraint JOIN pg_class ON pg_class.oid = conrelid WHERE relname IN ('catalog_category_entity', 'catalog_product_entity', 'catalog_product_attribute')");
    $constraints = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($constraints as $c) {
        echo " - " . $c['conname'] . " (" . $c['contype'] . "): " . $c['consrc'] . "\n";
    }
}
