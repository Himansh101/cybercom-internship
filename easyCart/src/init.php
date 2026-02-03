<?php
// Central Initialization File
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/Models/data.php';
require_once __DIR__ . '/Utils/shipping.utils.php';
require_once __DIR__ . '/Utils/coupon.utils.php';

// Global variables
$isLoggedIn = isset($_SESSION['user']);
$user = $_SESSION['user'] ?? null;

// Calculate total cart quantity (Distinct Items)
$cartQuantity = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cartQuantity = count($_SESSION['cart']);
}
?>
