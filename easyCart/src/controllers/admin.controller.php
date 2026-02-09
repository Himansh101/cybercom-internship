<?php
require_once __DIR__ . '/../init.php';

// Admin Access Control - Database based
if (!$isLoggedIn) {
    header("Location: login");
    exit();
}

if (!$user['is_admin']) {
    header("Location: index");
    exit();
}

// Page meta
$pageTitle = 'EasyCart | Admin Panel';
$currentPage = 'admin';
$extraStyles = ['admin.css'];
$extraScripts = ['admin.js'];

// Fetch summary stats for dashboard
$totalProducts = $pdo->query("SELECT COUNT(*) FROM catalog_product_entity")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM sales_order")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM customer_entity")->fetchColumn();

// Load View
require_once __DIR__ . '/../views/admin.view.php';
?>