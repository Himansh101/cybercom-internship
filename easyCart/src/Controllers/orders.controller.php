<?php
require_once __DIR__ . '/../init.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// 1. Fetch orders from JSON
$userId = $_SESSION['user']['id'];
$userOrders = [];
global $products; // To lookup product details in the view

$file = __DIR__ . '/../../users.json';
if (file_exists($file)) {
    $users = json_decode(file_get_contents($file), true) ?? [];
    foreach ($users as $u) {
        if ($u['id'] === $userId) {
            $userOrders = $u['orders'] ?? [];
            // Sort by date descending
            usort($userOrders, function ($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
            break;
        }
    }
}

// Page meta
$pageTitle = 'EasyCart | My Orders';
$currentPage = 'orders';
$extraStyles = ['orders.css'];
$extraScripts = [];

// Load View
require_once __DIR__ . '/../Views/orders.view.php';
?>
