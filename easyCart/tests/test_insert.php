<?php
require_once __DIR__ . '/../src/config/database.php';
$db = new Database();
$conn = $db->getConnection();
if ($conn) {
    try {
        $conn->beginTransaction();
        echo "Testing manual insert into catalog_category_entity...\n";
        $stmt = $conn->prepare("INSERT INTO catalog_category_entity (name, description) VALUES (:name, :desc) RETURNING entity_id");
        $stmt->execute([':name' => 'Test Cat', ':desc' => 'Test Desc']);
        $id = $stmt->fetchColumn();
        echo "Inserted ID: $id\n";
        $conn->rollBack();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        if ($e instanceof PDOException) {
            echo "Error Code: " . $e->getCode() . "\n";
            echo "Full Info: ";
            print_r($e->errorInfo);
        }
    }
}
