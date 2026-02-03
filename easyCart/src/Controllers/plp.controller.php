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

/* 2. Sorting & Filtering Logic */
global $products;
$filteredProducts = [];
if (isset($products) && is_array($products)) {
    // 2a. Filtering
    foreach ($products as $id => $product) {
        $catMatch    = (empty($selectedCats) || in_array($product['cat_id'], $selectedCats));
        $brandMatch  = (empty($selectedBrands) || in_array($product['brand_id'], $selectedBrands));
        $searchMatch = empty($searchQuery) || (stripos($product['name'], $searchQuery) !== false);
        $priceMatch  = ($product['price'] >= $minPrice && $product['price'] <= $maxPrice);
        $status      = ($product['in_stock']) ? 'instock' : 'outofstock';
        $stockMatch  = (empty($selectedStock) || in_array($status, $selectedStock));

        if ($catMatch && $brandMatch && $searchMatch && $priceMatch && $stockMatch) {
            $filteredProducts[$id] = $product;
        }
    }

    // 2b. Sorting
    // To handle 'newest' properly without reversing the whole list, 
    // we capture the original keys before sorting to use them as tie-breakers.
    $originalOrder = array_flip(array_keys($products));

    uksort($filteredProducts, function ($idA, $idB) use ($sortBy, $originalOrder, $filteredProducts) {
        $a = $filteredProducts[$idA];
        $b = $filteredProducts[$idB];

        // Step 1: Priority - Stock Status
        if ($a['in_stock'] !== $b['in_stock']) {
            return $b['in_stock'] <=> $a['in_stock']; // In stock (true/1) first
        }

        // Step 2: Tie-breaker - User Selection
        switch ($sortBy) {
            case 'price_low':
                return $a['price'] <=> $b['price'];
            case 'price_high':
                return $b['price'] <=> $a['price'];
            case 'name_asc':
                return strcasecmp($a['name'], $b['name']);
            case 'name_desc':
                return strcasecmp($b['name'], $a['name']);
            case 'newest':
            default:
                return $originalOrder[$idB] <=> $originalOrder[$idA]; // Higher index = Newer
        }
    });
}

// 2c. Pagination Calculation
$totalVisible = count($filteredProducts);
$totalPages = ceil($totalVisible / $productsPerPage);
$startItem = $totalVisible > 0 ? ($pageNumber - 1) * $productsPerPage + 1 : 0;
$endItem = min($pageNumber * $productsPerPage, $totalVisible);
$offset = ($pageNumber - 1) * $productsPerPage;
$paginatedProducts = array_slice($filteredProducts, $offset, $productsPerPage, true);

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
