<?php
require_once __DIR__ . '/../init.php';

// Admin Access Control - Database based
if (!$isLoggedIn || !$user['is_admin']) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied']);
    exit();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'import_products':
        header('Content-Type: application/json');

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['status' => 'error', 'message' => 'No file uploaded or upload error.']);
            exit();
        }

        $file = $_FILES['csv_file'];

        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, ['text/csv', 'text/plain', 'application/vnd.ms-excel'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Please upload a CSV file.']);
            exit();
        }

        // Parse CSV
        $handle = fopen($file['tmp_name'], 'r');
        $header = fgetcsv($handle); // First row is header

        // Normalize header
        $header = array_map('strtolower', array_map('trim', $header));

        $required = ['sku', 'name', 'price'];
        foreach ($required as $col) {
            if (!in_array($col, $header)) {
                echo json_encode(['status' => 'error', 'message' => "Missing required column: $col"]);
                exit();
            }
        }

        $inserted = 0;
        $skipped = 0;
        $errors = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            $data = array_combine($header, array_pad($row, count($header), ''));

            $sku = trim($data['sku'] ?? '');
            $name = trim($data['name'] ?? '');
            $price = floatval($data['price'] ?? 0);
            $stockCount = intval($data['stock_count'] ?? 0);
            $category = trim($data['category'] ?? '');
            $brandName = trim($data['brand_name'] ?? '');
            $description = trim($data['description'] ?? '');
            $imageUrl = trim($data['image_url'] ?? '');
            $shippingType = trim($data['shipping_type'] ?? 'standard');
            $inStock = isset($data['in_stock']) ? intval($data['in_stock']) : ($stockCount > 0 ? 1 : 0);

            // Validation
            if (empty($sku) || empty($name)) {
                $errors[] = "Row $rowNum: Missing SKU or Name.";
                continue;
            }
            if ($price <= 0) {
                $errors[] = "Row $rowNum: Invalid price.";
                continue;
            }

            // Check for duplicate SKU
            $stmt = $pdo->prepare("SELECT entity_id, url_key FROM catalog_product_entity WHERE sku = ?");
            $stmt->execute([$sku]);
            $existingProduct = $stmt->fetch(PDO::FETCH_ASSOC);
            $productId = null;

            try {
                $pdo->beginTransaction();

                if ($existingProduct) {
                    // Update existing product
                    $productId = $existingProduct['entity_id'];
                    $stmt = $pdo->prepare("UPDATE catalog_product_entity SET name = ?, price = ?, stock_count = ? WHERE entity_id = ?");
                    $stmt->execute([$name, $price, $stockCount, $productId]);

                    // Logic to update/generate URL Key
                    $newUrlKey = trim($data['url_key'] ?? '');
                    if (empty($newUrlKey) && empty($existingProduct['url_key'])) {
                        // Generate if both new and old are empty
                        $newUrlKey = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
                        // Ensure Uniqueness
                        $baseKey = $newUrlKey;
                        $counter = 1;
                        while (true) {
                            $stmtCheck = $pdo->prepare("SELECT entity_id FROM catalog_product_entity WHERE url_key = ? AND entity_id != ?");
                            $stmtCheck->execute([$newUrlKey, $productId]);
                            if (!$stmtCheck->fetch()) {
                                break;
                            }
                            $newUrlKey = $baseKey . '-' . $counter;
                            $counter++;
                        }
                    }

                    if (!empty($newUrlKey)) {
                        $stmtUrl = $pdo->prepare("UPDATE catalog_product_entity SET url_key = ? WHERE entity_id = ?");
                        $stmtUrl->execute([$newUrlKey, $productId]);
                    }

                    // Remove old attributes/relations to replace?
                    // Or UPSERT attributes.
                    // Easiest is to DELETE old attributes and re-insert?
                    // "replace older ones" implies full overwrite of provided fields.
                    // But deleting everything might lose data not in CSV?
                    // CSV has all main fields. EAV Attributes: description, brand, shipping, in_stock.
                    // I will delete these specific attributes and re-insert.

                    $pdo->prepare("DELETE FROM catalog_product_attribute WHERE entity_id = ? AND attribute_key IN ('description', 'brand_id', 'shipping_type', 'in_stock')")->execute([$productId]);

                    // Categories?
                    // If category is provided, should we switch category?
                    if (!empty($category)) {
                        $pdo->prepare("DELETE FROM catalog_category_product WHERE product_id = ?")->execute([$productId]);
                    }

                    // Image?
                    if (!empty($imageUrl)) {
                        $pdo->prepare("DELETE FROM catalog_product_image WHERE product_id = ? AND is_main_image = true")->execute([$productId]);
                    }

                } else {
                    // Generate URL Key
                    $urlKey = trim($data['url_key'] ?? '');
                    if (empty($urlKey)) {
                        $urlKey = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
                    }

                    // Ensure Uniqueness
                    $baseKey = $urlKey;
                    $counter = 1;
                    while (true) {
                        $stmtCheck = $pdo->prepare("SELECT entity_id FROM catalog_product_entity WHERE url_key = ?");
                        $stmtCheck->execute([$urlKey]);
                        if (!$stmtCheck->fetch()) {
                            break;
                        }
                        $urlKey = $baseKey . '-' . $counter;
                        $counter++;
                    }

                    // 1. Insert product
                    $stmt = $pdo->prepare("INSERT INTO catalog_product_entity (sku, name, price, stock_count, url_key) VALUES (?, ?, ?, ?, ?) RETURNING entity_id");
                    $stmt->execute([$sku, $name, $price, $stockCount, $urlKey]);
                    $productId = $stmt->fetchColumn();
                }

                // 2. Insert attributes
                $stmtAttr = $pdo->prepare("INSERT INTO catalog_product_attribute (entity_id, attribute_key, attribute_value) VALUES (?, ?, ?)");

                if (!empty($description)) {
                    $stmtAttr->execute([$productId, 'description', $description]);
                }
                if (!empty($brandName)) {
                    // Find or create brand
                    $stmtBrand = $pdo->prepare("SELECT entity_id FROM catalog_brand_entity WHERE LOWER(name) = LOWER(?)");
                    $stmtBrand->execute([$brandName]);
                    $brandId = $stmtBrand->fetchColumn();

                    if (!$brandId) {
                        // Create brand ID (slug-like)
                        $brandId = 'br_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $brandName)) . '_' . substr(uniqid(), -4);
                        $stmtBrand = $pdo->prepare("INSERT INTO catalog_brand_entity (entity_id, name) VALUES (?, ?)");
                        $stmtBrand->execute([$brandId, $brandName]);
                    }
                    $stmtAttr->execute([$productId, 'brand_id', $brandId]);
                }
                $stmtAttr->execute([$productId, 'shipping_type', $shippingType]);
                $stmtAttr->execute([$productId, 'in_stock', (string) $inStock]);

                // 3. Insert category link
                if (!empty($category)) {
                    // Find or create category
                    $stmtCat = $pdo->prepare("SELECT entity_id FROM catalog_category_entity WHERE LOWER(name) = LOWER(?)");
                    $stmtCat->execute([$category]);
                    $catId = $stmtCat->fetchColumn();

                    if (!$catId) {
                        $stmtCat = $pdo->prepare("INSERT INTO catalog_category_entity (name) VALUES (?) RETURNING entity_id");
                        $stmtCat->execute([$category]);
                        $catId = $stmtCat->fetchColumn();
                    }

                    $pdo->prepare("INSERT INTO catalog_category_product (category_id, product_id) VALUES (?, ?)")->execute([$catId, $productId]);
                }

                // 4. Insert image
                if (!empty($imageUrl)) {
                    $pdo->prepare("INSERT INTO catalog_product_image (product_id, image_url, is_main_image) VALUES (?, ?, true)")->execute([$productId, $imageUrl]);
                }

                $pdo->commit();
                $inserted++;

            } catch (Exception $e) {
                if ($pdo->inTransaction())
                    $pdo->rollBack();
                $errors[] = "Row $rowNum: " . $e->getMessage();
            }
        }

        fclose($handle);

        echo json_encode([
            'status' => 'success',
            'inserted' => $inserted,
            'updated' => $skipped, // Renaming skipped to updated in variable meaning, but keeping variable name to minimize diff, or better rename it.
            // Let's rely on logic context: we track "inserted" for new.
            // But I used $skipped variable for duplicates.
            // Now duplicates are updates.
            'skipped' => 0, // No longer skipping
            'updated_count' => $skipped, // I'll use separate key if client reads it
            'errors' => array_slice($errors, 0, 10) // Limit errors shown
        ]);
        break;

    case 'export_products':
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="products_export_' . date('Ymd_His') . '.csv"');

        $output = fopen('php://output', 'w');

        // Header row
        fputcsv($output, ['sku', 'name', 'price', 'stock_count', 'category', 'brand_name', 'description', 'image_url', 'shipping_type', 'in_stock', 'url_key']);

        // Fetch all products with related data
        $sql = "SELECT 
                    p.sku, p.name, p.price, p.stock_count,
                    c.name as category,
                    b.name as brand_name,
                    (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'description') as description,
                    i.image_url,
                    (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'shipping_type') as shipping_type,
                    (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'in_stock') as in_stock,
                    p.url_key
                FROM catalog_product_entity p
                LEFT JOIN catalog_category_product cp ON p.entity_id = cp.product_id
                LEFT JOIN catalog_category_entity c ON cp.category_id = c.entity_id
                LEFT JOIN catalog_product_attribute b_attr ON p.entity_id = b_attr.entity_id AND b_attr.attribute_key = 'brand_id'
                LEFT JOIN catalog_brand_entity b ON b_attr.attribute_value = b.entity_id
                LEFT JOIN catalog_product_image i ON p.entity_id = i.product_id AND i.is_main_image = true
                ORDER BY p.entity_id";

        $stmt = $pdo->query($sql);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, [
                $row['sku'],
                $row['name'],
                $row['price'],
                $row['stock_count'],
                $row['category'] ?? '',
                $row['brand_name'] ?? '',
                $row['description'] ?? '',
                $row['image_url'] ?? '',
                $row['shipping_type'] ?? 'standard',
                $row['in_stock'] ?? '1',
                $row['url_key'] ?? ''
            ]);
        }

        fclose($output);
        exit();
        break;

    case 'download_template':
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="product_import_template.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['sku', 'name', 'price', 'stock_count', 'category', 'brand_name', 'description', 'image_url', 'shipping_type', 'in_stock', 'url_key']);
        fputcsv($output, ['SKU-SAMPLE', 'Sample Product', '999.00', '10', 'Electronics', 'Aurora', 'Product description here', 'images/sample.jpg', 'standard', '1', 'sample-product']);
        fclose($output);
        exit();

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
?>