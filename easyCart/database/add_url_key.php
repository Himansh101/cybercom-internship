<?php
require_once __DIR__ . '/../src/config/database.php';

$db = new Database();
$conn = $db->getConnection();

if (!$conn) {
    die("Connection failed!");
}

echo "Adding url_key column if not exists...\n";
try {
    // Check if column exists (PostgreSQL syntax)
    $checkColumn = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name='catalog_product_entity' AND column_name='url_key'");
    if (!$checkColumn->fetch()) {
        $conn->exec("ALTER TABLE catalog_product_entity ADD COLUMN url_key VARCHAR(255) UNIQUE");
        echo "Column url_key added.\n";
    } else {
        echo "Column url_key already exists.\n";
    }

    echo "Populating url_key for existing products...\n";
    $stmt = $conn->query("SELECT entity_id, name FROM catalog_product_entity WHERE url_key IS NULL OR url_key = ''");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $updateStmt = $conn->prepare("UPDATE catalog_product_entity SET url_key = :url_key WHERE entity_id = :id");

    foreach ($products as $p) {
        $slug = strtolower(trim($p['name']));
        $slug = preg_replace('/[^a-z0-0-]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        // Check for uniqueness
        $baseSlug = $slug;
        $counter = 1;
        while (true) {
            $checkStmt = $conn->prepare("SELECT COUNT(*) FROM catalog_product_entity WHERE url_key = ? AND entity_id != ?");
            $checkStmt->execute([$slug, $p['entity_id']]);
            if ($checkStmt->fetchColumn() == 0) {
                break;
            }
            $slug = $baseSlug . '-' . $counter++;
        }

        echo " - Updating '{$p['name']}' -> '{$slug}'\n";
        $updateStmt->execute([':url_key' => $slug, ':id' => $p['entity_id']]);
    }

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
