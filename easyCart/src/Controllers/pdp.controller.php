<?php
require_once __DIR__ . '/../init.php';

// 1. Get the product ID from the URL
$productId = $_GET['id'] ?? null;

// 2. Validate Product Existence
global $products, $categories, $brands;
if ($productId === null || !isset($products[$productId])) {
    header("Location: plp.php");
    exit();
}

$product = $products[$productId];

// Page meta
$pageTitle = 'EasyCart | ' . htmlspecialchars($product['name']);
$currentPage = 'products';
$extraStyles = ['pdp.css'];
$extraScripts = ['pdp.js'];

// 3. Check current quantity in cart
$currentQtyInCart = $_SESSION['cart'][$productId] ?? 0;

// Lookup Logic
$categoryName = $categories[$product['cat_id']] ?? 'Uncategorized';
$brandName    = $brands[$product['brand_id']]['name'] ?? 'Generic';

// Load View
require_once __DIR__ . '/../Views/pdp.view.php';
?>
