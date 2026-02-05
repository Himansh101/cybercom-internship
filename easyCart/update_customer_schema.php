<?php
require_once __DIR__ . '/src/init.php';

echo "Updating schema to add address fields to customer_entity...\n";

try {
    // Check if columns exist
    $columns = ['street_address' => 'TEXT', 'city' => 'VARCHAR(100)', 'pincode' => 'VARCHAR(20)'];
    
    foreach ($columns as $col => $type) {
        $stmt = $pdo->prepare("
            SELECT column_name 
            FROM information_schema.columns 
            WHERE table_name='customer_entity' AND column_name=?
        ");
        $stmt->execute([$col]);
        
        if (!$stmt->fetch()) {
            echo "Adding column: $col... ";
            $pdo->exec("ALTER TABLE customer_entity ADD COLUMN $col $type NULL");
            echo "Done.\n";
        } else {
            echo "Column $col already exists.\n";
        }
    }
    
    echo "Schema update complete.\n";

} catch (PDOException $e) {
    echo "Error updating schema: " . $e->getMessage() . "\n";
}
