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
            $brandId = trim($data['brand_id'] ?? '');
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
            $stmt = $pdo->prepare("SELECT entity_id FROM catalog_product_entity WHERE sku = ?");
            $stmt->execute([$sku]);
            if ($stmt->fetch()) {
                $skipped++;
                continue;
            }

            try {
                $pdo->beginTransaction();

                // 1. Insert product
                $stmt = $pdo->prepare("INSERT INTO catalog_product_entity (sku, name, price, stock_count) VALUES (?, ?, ?, ?) RETURNING entity_id");
                $stmt->execute([$sku, $name, $price, $stockCount]);
                $productId = $stmt->fetchColumn();

                // 2. Insert attributes
                $stmtAttr = $pdo->prepare("INSERT INTO catalog_product_attribute (entity_id, attribute_key, attribute_value) VALUES (?, ?, ?)");
                
                if (!empty($description)) {
                    $stmtAttr->execute([$productId, 'description', $description]);
                }
                if (!empty($brandId)) {
                    $stmtAttr->execute([$productId, 'brand_id', $brandId]);
                }
                $stmtAttr->execute([$productId, 'shipping_type', $shippingType]);
                $stmtAttr->execute([$productId, 'in_stock', (string)$inStock]);

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
                if ($pdo->inTransaction()) $pdo->rollBack();
                $errors[] = "Row $rowNum: " . $e->getMessage();
            }
        }

        fclose($handle);

        echo json_encode([
            'status' => 'success',
            'inserted' => $inserted,
            'skipped' => $skipped,
            'errors' => array_slice($errors, 0, 10) // Limit errors shown
        ]);
        break;

    case 'export_products':
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="products_export_' . date('Ymd_His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Header row
        fputcsv($output, ['sku', 'name', 'price', 'stock_count', 'category', 'brand_id', 'description', 'image_url', 'shipping_type', 'in_stock']);
        
        // Fetch all products with related data
        $sql = "SELECT 
                    p.sku, p.name, p.price, p.stock_count,
                    c.name as category,
                    (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'brand_id') as brand_id,
                    (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'description') as description,
                    i.image_url,
                    (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'shipping_type') as shipping_type,
                    (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'in_stock') as in_stock
                FROM catalog_product_entity p
                LEFT JOIN catalog_category_product cp ON p.entity_id = cp.product_id
                LEFT JOIN catalog_category_entity c ON cp.category_id = c.entity_id
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
                $row['brand_id'] ?? '',
                $row['description'] ?? '',
                $row['image_url'] ?? '',
                $row['shipping_type'] ?? 'standard',
                $row['in_stock'] ?? '1'
            ]);
        }
        
        fclose($output);
        exit();

    case 'download_template':
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="product_import_template.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['sku', 'name', 'price', 'stock_count', 'category', 'brand_id', 'description', 'image_url', 'shipping_type', 'in_stock']);
        fputcsv($output, ['SKU-SAMPLE', 'Sample Product', '999.00', '10', 'Electronics', 'br_01', 'Product description here', 'images/sample.jpg', 'standard', '1']);
        fclose($output);
        exit();

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
?>
