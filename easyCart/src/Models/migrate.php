<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/data.php';

$db = new Database();
$conn = $db->getConnection();

if (!$conn) {
    die("Connection failed!");
}

echo "Starting migration...\n";

try {
    $conn->beginTransaction();

    // 0. Clean slate & Table Creation
    echo "Creating/Resetting schema...\n";
    $schema = file_get_contents(__DIR__ . '/schema.sql');
    if (!$schema) {
        throw new Exception("Could not read schema.sql");
    }
    $conn->exec($schema);
    echo "Schema rebuilt successfully.\n";

    // 1. Migrate Categories
    echo "Migrating categories...\n";
    $categoryMap = [];
    $stmt = $conn->prepare("INSERT INTO catalog_category_entity (name, description) VALUES (:name, :desc) RETURNING entity_id");
    foreach ($categories as $slug => $name) {
        echo " - Category: $name... ";
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':desc', "Category: $name", PDO::PARAM_STR);
        $stmt->execute();
        $categoryMap[$slug] = $stmt->fetchColumn();
        echo "Done ($categoryMap[$slug])\n";
    }

    // 1b. Migrate Brands
    echo "Migrating brands...\n";
    $stmtBrand = $conn->prepare("INSERT INTO catalog_brand_entity (entity_id, name, description) VALUES (:id, :name, :desc)");
    foreach ($brands as $id => $b) {
        echo " - Brand: " . $b['name'] . "... ";
        $stmtBrand->bindValue(':id', $id, PDO::PARAM_STR);
        $stmtBrand->bindValue(':name', $b['name'], PDO::PARAM_STR);
        $stmtBrand->bindValue(':desc', $b['tag'], PDO::PARAM_STR);
        $stmtBrand->execute();
        echo "Done\n";
    }

    // 2. Migrate Products
    echo "Migrating products...\n";
    $stmtProd = $conn->prepare("INSERT INTO catalog_product_entity (sku, name, price, stock_count) VALUES (:sku, :name, :price, :stock) RETURNING entity_id");
    $stmtCat = $conn->prepare("INSERT INTO catalog_category_product (category_id, product_id) VALUES (:cat_id, :prod_id)");
    $stmtAttr = $conn->prepare("INSERT INTO catalog_product_attribute (entity_id, attribute_key, attribute_value) VALUES (:prod_id, :key, :val)");
    $stmtImg = $conn->prepare("INSERT INTO catalog_product_image (product_id, image_url, is_main_image) VALUES (:prod_id, :url, :is_main)");

    foreach ($products as $id => $p) {
        // Entity
        $sku = 'SKU-' . $id;
        echo " - Product: $sku (" . $p['name'] . ")... ";
        $stmtProd->bindValue(':sku', $sku, PDO::PARAM_STR);
        $stmtProd->bindValue(':name', $p['name'], PDO::PARAM_STR);
        $stmtProd->bindValue(':price', $p['price']); // Defaults to string, but decimal accepts it
        $stmtProd->bindValue(':stock', (int)$p['stock_count'], PDO::PARAM_INT);
        $stmtProd->execute();
        $productId = $stmtProd->fetchColumn();
        echo "Saved ($productId)\n";

        // Category link
        if (isset($categoryMap[$p['cat_id']])) {
            $stmtCat->bindValue(':cat_id', (int)$categoryMap[$p['cat_id']], PDO::PARAM_INT);
            $stmtCat->bindValue(':prod_id', (int)$productId, PDO::PARAM_INT);
            $stmtCat->execute();
        }

        // Attributes
        $attrs = [
            'description' => $p['description'] ?? '',
            'is_featured' => ($p['is_featured'] ?? false) ? '1' : '0',
            'in_stock' => ($p['in_stock'] ?? false) ? '1' : '0',
            'shipping_type' => $p['item_shipping_type'] ?? 'standard',
            'brand_id' => $p['brand_id'] ?? ''
        ];
        foreach ($attrs as $k => $v) {
            $stmtAttr->bindValue(':prod_id', (int)$productId, PDO::PARAM_INT);
            $stmtAttr->bindValue(':key', $k, PDO::PARAM_STR);
            $stmtAttr->bindValue(':val', (string)$v, PDO::PARAM_STR);
            $stmtAttr->execute();
        }

        // Images
        $stmtImg->bindValue(':prod_id', (int)$productId, PDO::PARAM_INT);
        $stmtImg->bindValue(':url', $p['image'], PDO::PARAM_STR);
        $stmtImg->bindValue(':is_main', true, PDO::PARAM_BOOL);
        $stmtImg->execute();
        if (isset($p['images'])) {
            foreach ($p['images'] as $img) {
                if ($img !== $p['image']) {
                    $stmtImg->bindValue(':prod_id', (int)$productId, PDO::PARAM_INT);
                    $stmtImg->bindValue(':url', $img, PDO::PARAM_STR);
                    $stmtImg->bindValue(':is_main', false, PDO::PARAM_BOOL);
                    $stmtImg->execute();
                }
            }
        }
    }

    // 3. Migrate Customers & Orders
    echo "Migrating customers...\n";
    $usersFile = __DIR__ . '/../../users.json';
    if (file_exists($usersFile)) {
        $users = json_decode(file_get_contents($usersFile), true) ?? [];
        $stmtUser = $conn->prepare("INSERT INTO customer_entity (name, email, mobile, password, created_at) VALUES (:name, :email, :mobile, :pass, :created)");
        $stmtOrder = $conn->prepare("INSERT INTO sales_order (user_id, order_number, subtotal, shipping_cost, tax_amount, final_amount, status, created_at) VALUES (:u_id, :o_num, :sub, :ship, :tax, :final, :status, :created) RETURNING order_id");
        $stmtOrderItem = $conn->prepare("INSERT INTO sales_order_item (order_id, product_name_snapshot, price_snapshot, quantity) VALUES (:o_id, :p_name, :price, :qty)");
        $stmtOrderAddr = $conn->prepare("INSERT INTO sales_order_address (order_id, full_name, street_address, city, pincode) VALUES (:o_id, :name, :addr, :city, :pincode)");

        foreach ($users as $u) {
            echo " - User: " . $u['email'] . "... ";
            $stmtUser->bindValue(':name', $u['name'], PDO::PARAM_STR);
            $stmtUser->bindValue(':email', $u['email'], PDO::PARAM_STR);
            $stmtUser->bindValue(':mobile', $u['mobile'] ?? '', PDO::PARAM_STR);
            $stmtUser->bindValue(':pass', $u['password'], PDO::PARAM_STR);
            $stmtUser->bindValue(':created', $u['created_at'], PDO::PARAM_STR);
            $stmtUser->execute();
            $customerId = $conn->lastInsertId();
            echo "Saved ($customerId)\n";

            if (isset($u['orders'])) {
                foreach ($u['orders'] as $order) {
                    $subtotal = round($order['total'] / 1.18, 2);
                    $tax = round($order['total'] - $subtotal, 2);
                    $stmtOrder->bindValue(':u_id', (int)$customerId, PDO::PARAM_INT);
                    $stmtOrder->bindValue(':o_num', $order['order_id'], PDO::PARAM_STR);
                    $stmtOrder->bindValue(':sub', $subtotal);
                    $stmtOrder->bindValue(':ship', $order['total'] > 500 ? 50 : 0);
                    $stmtOrder->bindValue(':tax', $tax);
                    $stmtOrder->bindValue(':final', $order['total']);
                    $stmtOrder->bindValue(':status', $order['status'], PDO::PARAM_STR);
                    $stmtOrder->bindValue(':created', $order['date'], PDO::PARAM_STR);
                    $stmtOrder->execute();
                    $orderId = $stmtOrder->fetchColumn();

                    foreach ($order['items'] as $item) {
                        $stmtOrderItem->bindValue(':o_id', (int)$orderId, PDO::PARAM_INT);
                        $stmtOrderItem->bindValue(':p_name', 'Product #' . $item['id'], PDO::PARAM_STR);
                        $stmtOrderItem->bindValue(':price', $item['price']);
                        $stmtOrderItem->bindValue(':qty', (int)$item['qty'], PDO::PARAM_INT);
                        $stmtOrderItem->execute();
                    }

                    if (isset($order['address'])) {
                        $stmtOrderAddr->bindValue(':o_id', (int)$orderId, PDO::PARAM_INT);
                        $stmtOrderAddr->bindValue(':name', $order['address']['name'], PDO::PARAM_STR);
                        $stmtOrderAddr->bindValue(':addr', $order['address']['address'], PDO::PARAM_STR);
                        $stmtOrderAddr->bindValue(':city', $order['address']['city'], PDO::PARAM_STR);
                        $stmtOrderAddr->bindValue(':pincode', $order['address']['pincode'], PDO::PARAM_STR);
                        $stmtOrderAddr->execute();
                    }
                }
            }
        }
    }

    $conn->commit();
    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
