<?php
require_once __DIR__ . '/../init.php';

// Page meta
$pageTitle = 'EasyCart | Home';
$currentPage = 'home';

// 1. Initialize search query
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

// 2. Fetch featured products from Database
$stmt = $pdo->query("SELECT p.*, i.image_url as image,
    (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'in_stock') as in_stock,
    (SELECT category_id FROM catalog_category_product WHERE product_id = p.entity_id LIMIT 1) as cat_id
    FROM catalog_product_entity p
    LEFT JOIN catalog_product_image i ON p.entity_id = i.product_id AND i.is_main_image = true
    WHERE EXISTS (SELECT 1 FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'is_featured' AND attribute_value = '1')
    LIMIT 4");
$dbFeatured = $stmt->fetchAll(PDO::FETCH_ASSOC);

$featuredProducts = [];
foreach ($dbFeatured as $row) {
    $featuredProducts[$row['entity_id']] = [
        'id' => $row['entity_id'],
        'name' => $row['name'],
        'price' => $row['price'],
        'image' => (strpos($row['image'], 'http') === 0) ? $row['image'] : $row['image'],
        'in_stock' => ($row['in_stock'] === '1'),
        'cat_id' => $row['cat_id']
    ];
}

// 3. Fetch Categories for section
$stmtCats = $pdo->query("SELECT entity_id, name FROM catalog_category_entity ORDER BY name LIMIT 6");
$categories = $stmtCats->fetchAll(PDO::FETCH_KEY_PAIR);

// 4. Fetch Brands for section
$stmtBrands = $pdo->query("SELECT entity_id, name FROM catalog_brand_entity ORDER BY name LIMIT 6");
$brandRows = $stmtBrands->fetchAll(PDO::FETCH_ASSOC);
$brands = [];
foreach ($brandRows as $b) {
    $brands[$b['entity_id']] = ['name' => $b['name']];
}


$extraStyles = ['plp.css'];
$extraScripts = ['plp.js'];

// Load View
require_once __DIR__ . '/../views/index.view.php';
?>
