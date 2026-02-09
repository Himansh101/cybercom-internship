<?php
require_once __DIR__ . '/../init.php';

// Page meta
$pageTitle = 'EasyCart | Shop';
$currentPage = 'products';
$extraStyles = ['plp.css'];
$extraScripts = ['plp.js'];

/** 1. Initialize variables from GET parameters */
$selectedCats   = $_GET['categories'] ?? [];
$selectedBrands = $_GET['brands'] ?? [];
$selectedStock  = $_GET['stock_status'] ?? [];
$searchQuery    = isset($_GET['search']) ? trim($_GET['search']) : '';

$minPrice = (isset($_GET['min_price']) && $_GET['min_price'] !== '') ? (int)$_GET['min_price'] : 0;
$maxPrice = (isset($_GET['max_price']) && $_GET['max_price'] !== '') ? (int)$_GET['max_price'] : 1000000;
$sortBy   = $_GET['sort'] ?? 'newest';
$pageNumber = (isset($_GET['page']) && $_GET['page'] > 0) ? (int)$_GET['page'] : 1;
$productsPerPage = 9;

// Fetch categories for filter
$stmtCats = $pdo->query("SELECT entity_id, name FROM catalog_category_entity ORDER BY name");
$categories = $stmtCats->fetchAll(PDO::FETCH_KEY_PAIR);

// Fetch brands for filter
$stmtBrands = $pdo->query("SELECT entity_id, name FROM catalog_brand_entity ORDER BY name");
$brandRows = $stmtBrands->fetchAll(PDO::FETCH_ASSOC);
$brands = [];
foreach ($brandRows as $b) {
    $brands[$b['entity_id']] = ['name' => $b['name']];
}

/* 2. Sorting & Filtering Logic (PostgreSQL Driven) */
$whereClauses = ["1=1"];
$params = [];

// Filtering
if (!empty($selectedCats)) {
    $placeholders = implode(',', array_fill(0, count($selectedCats), '?'));
    $whereClauses[] = "p.entity_id IN (SELECT product_id FROM catalog_category_product WHERE category_id IN ($placeholders))";
    foreach ($selectedCats as $cat) $params[] = (int)$cat;
}

if (!empty($searchQuery)) {
    $whereClauses[] = "p.name ILIKE ?";
    $params[] = '%' . $searchQuery . '%';
}

if ($minPrice >= 0 && $maxPrice > 0) {
    $whereClauses[] = "p.price BETWEEN ? AND ?";
    $params[] = $minPrice;
    $params[] = $maxPrice;
}

// Brand Filtering
if (!empty($selectedBrands)) {
    $placeholders = implode(',', array_fill(0, count($selectedBrands), '?'));
    $whereClauses[] = "p.entity_id IN (SELECT entity_id FROM catalog_product_attribute WHERE attribute_key = 'brand_id' AND attribute_value IN ($placeholders))";
    foreach ($selectedBrands as $brandId) $params[] = $brandId;
}

// Stock Status Filtering
if (!empty($selectedStock)) {
    $subConditions = [];
    if (in_array('instock', $selectedStock)) {
        // Must have attribute '1' AND numeric count > 0
        $subConditions[] = "(p.entity_id IN (SELECT entity_id FROM catalog_product_attribute WHERE attribute_key = 'in_stock' AND attribute_value = '1') AND p.stock_count > 0)";
    }
    if (in_array('outofstock', $selectedStock)) {
        // Has attribute '0' OR numeric count <= 0
        $subConditions[] = "(p.entity_id IN (SELECT entity_id FROM catalog_product_attribute WHERE attribute_key = 'in_stock' AND attribute_value = '0') OR p.stock_count <= 0)";
    }
    
    if (!empty($subConditions)) {
        $whereClauses[] = "(" . implode(" OR ", $subConditions) . ")";
    }
}

// Sorting logic with Stock Priority
$stockPrioritySql = "(CASE WHEN p.stock_count > 0 THEN 1 ELSE 0 END) DESC";

switch ($sortBy) {
    case 'price_low': $sortSql = "p.price ASC"; break;
    case 'price_high': $sortSql = "p.price DESC"; break;
    case 'name_asc': $sortSql = "p.name ASC"; break;
    case 'name_desc': $sortSql = "p.name DESC"; break;
    case 'newest':
    default: $sortSql = "p.created_at DESC"; break;
}

// Total Count for Pagination
$countSql = "SELECT COUNT(*) FROM catalog_product_entity p WHERE " . implode(" AND ", $whereClauses);
$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute($params);
$totalVisible = (int)$stmtCount->fetchColumn();

// Main Fetch Query
$sql = "SELECT p.*, i.image_url as image, 
        (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'in_stock') as in_stock,
        (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'shipping_type') as shipping_type,
        (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'brand_id') as brand_id,
        (SELECT category_id FROM catalog_category_product WHERE product_id = p.entity_id LIMIT 1) as cat_id
        FROM catalog_product_entity p
        LEFT JOIN catalog_product_image i ON p.entity_id = i.product_id AND i.is_main_image = true
        WHERE " . implode(" AND ", $whereClauses) . "
        ORDER BY $stockPrioritySql, $sortSql
        LIMIT $productsPerPage OFFSET " . (($pageNumber - 1) * $productsPerPage);

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productsFromDb = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Map back to expected structure for view
$filteredProducts = [];
foreach ($productsFromDb as $row) {
    $filteredProducts[$row['entity_id']] = [
        'id' => $row['entity_id'],
        'name' => $row['name'],
        'price' => $row['price'],
        'image' => (strpos($row['image'], 'http') === 0) ? $row['image'] : $row['image'],
        // Fail-safe: Check both the attribute and the actual stock_count
        'in_stock' => ($row['in_stock'] === '1' && (int)$row['stock_count'] > 0),
        'brand_id' => $row['brand_id'],
        'cat_id' => $row['cat_id'], // Added to ensure view can look up category
        'item_shipping_type' => $row['shipping_type']
    ];
}

// 2c. Pagination Calculation
$totalPages = ceil($totalVisible / $productsPerPage);
$startItem = $totalVisible > 0 ? ($pageNumber - 1) * $productsPerPage + 1 : 0;
$endItem = min($pageNumber * $productsPerPage, $totalVisible);
$paginatedProducts = $filteredProducts; // Already limited/offset in SQL

// 4. Handle AJAX Response
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    // Note: In a real refactor, we would move helper functions to `src/Utils/plp_helpers.php`
    // For now, index.view handles the rendering helper logic or we just re-include view parts.
    // Simplifying: re-render the view part for AJAX
    require_once __DIR__ . '/../views/plp.view.php';
    exit;
}

// Load View
require_once __DIR__ . '/../views/plp.view.php';
?>
