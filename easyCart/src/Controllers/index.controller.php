<?php
require_once __DIR__ . '/../init.php';

// Page meta
$pageTitle = 'EasyCart | Home';
$currentPage = 'home';

// 1. Initialize search query
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

// 2. Filter products to get only the featured ones
global $products; // From data.php included in init.php
$featuredProducts = array_filter($products, function ($p) {
    return isset($p['is_featured']) && $p['is_featured'] === true;
});


$extraStyles = ['plp.css'];
$extraScripts = ['plp.js'];

// Load View
require_once __DIR__ . '/../views/index.view.php';
?>
