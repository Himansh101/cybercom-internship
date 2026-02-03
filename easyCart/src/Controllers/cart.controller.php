<?php
require_once __DIR__ . '/../init.php';

// Page meta
$pageTitle = 'EasyCart | Shopping Cart';
$currentPage = 'cart';
$extraStyles = ['cart.css'];
$extraScripts = ['cart.js'];

$subtotal = 0;
$hasFreightItem = false;

// Calculate subtotal and check for freight items
global $products;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $id => $quantity) {
        if (isset($products[$id])) {
            $subtotal += $products[$id]['price'] * $quantity;

            // Check if this product requires freight shipping
            if (isset($products[$id]['item_shipping_type']) && $products[$id]['item_shipping_type'] === 'freight') {
                $hasFreightItem = true;
            }
        }
    }
}

// Determine default shipping method based on cart
if ($hasFreightItem || $subtotal > 300) {
    $defaultShippingMethod = 'white_glove';
} else {
    $defaultShippingMethod = 'standard';
}

// Calculate shipping cost
$shipping_fee = calculate_shipping_cost($defaultShippingMethod, $subtotal);

// Load View
require_once __DIR__ . '/../Views/cart.view.php';
?>
