<?php
require_once __DIR__ . '/../init.php';

// Page meta
$pageTitle = 'EasyCart | Product Detail';
$currentPage = 'products';
$extraStyles = ['pdp.css'];
$extraScripts = ['pdp.js'];

// 1. Get the product ID from the URL
$productId = isset($_GET['id']) ? (int)$_GET['id'] : null;

// 2. Validate Product Existence & Fetch Details
if ($productId === null) {
    header("Location: plp");
    exit();
}

$stmt = $pdo->prepare("SELECT p.*, 
    (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'description') as description,
    (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'in_stock') as in_stock,
    (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'brand_id') as brand_id,
    (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'shipping_type') as shipping_type
    FROM catalog_product_entity p WHERE p.entity_id = :id");
$stmt->execute([':id' => $productId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    header("Location: plp");
    exit();
}

// Fetch images
$stmtImg = $pdo->prepare("SELECT image_url, is_main_image FROM catalog_product_image WHERE product_id = :id ORDER BY is_main_image DESC");
$stmtImg->execute([':id' => $productId]);
$dbImages = $stmtImg->fetchAll(PDO::FETCH_ASSOC);

// Map components for view compatibility
$product = [
    'id' => $row['entity_id'],
    'name' => $row['name'],
    'price' => $row['price'],
    'image' => (strpos($dbImages[0]['image_url'], 'http') === 0) ? $dbImages[0]['image_url'] : $dbImages[0]['image_url'],
    'images' => array_map(fn($i) => (strpos($i['image_url'], 'http') === 0) ? $i['image_url'] : $i['image_url'], $dbImages),
    'description' => $row['description'],
    'in_stock' => ($row['in_stock'] === '1'),
    'stock_count' => (int)$row['stock_count'],
    'brand_id' => $row['brand_id'],
    'item_shipping_type' => $row['shipping_type']
];

// Fetch Category
$stmtCat = $pdo->prepare("SELECT name FROM catalog_category_entity WHERE entity_id = (SELECT category_id FROM catalog_category_product WHERE product_id = :id LIMIT 1)");
$stmtCat->execute([':id' => $productId]);
$categoryName = $stmtCat->fetchColumn() ?: 'Uncategorized';

// Brand Display Name
$stmtBrand = $pdo->prepare("SELECT name FROM catalog_brand_entity WHERE entity_id = :b_id");
$stmtBrand->execute([':b_id' => $row['brand_id']]);
$brandName = $stmtBrand->fetchColumn() ?: 'Generic';

// Cart Quantity from DB
$currentQtyInCart = 0;
if ($cartId) {
    $stmtQty = $pdo->prepare("SELECT quantity FROM sales_cart_product WHERE cart_id = ? AND product_id = ?");
    $stmtQty->execute([$cartId, $productId]);
    $currentQtyInCart = (int)$stmtQty->fetchColumn();
}

// Load View
require_once __DIR__ . '/../views/pdp.view.php';
?>
