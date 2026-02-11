<?php
require_once __DIR__ . '/src/init.php';
$db = new \App\Database();
$pdo = $db->getConnection();

function printCols($pdo, $table)
{
    echo "--- $table ---\n";
    try {
        $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name = '$table'");
        $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($cols as $c) {
            echo $c . "\n";
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
    echo "\n";
}

printCols($pdo, 'customer_entity');
printCols($pdo, 'sales_order_address');
printCols($pdo, 'customer_address_entity'); // To confirming non-existence
