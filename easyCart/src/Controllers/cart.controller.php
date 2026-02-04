<?php
require_once __DIR__ . '/../init.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login");
    exit();
}

// Page meta
$pageTitle = 'EasyCart | Shopping Cart';
$currentPage = 'cart';
$extraStyles = ['cart.css'];
$extraScripts = ['cart.js'];

$subtotal = 0;
$hasFreightItem = false;

// 1. Fetch Cart Items from Database
$cartItemsFromDb = loadCartArrayFromDb($pdo, $cartId);

$cartItems = [];
if (!empty($cartItemsFromDb)) {
    foreach ($cartItemsFromDb as $id => $quantity) {
        $stmt = $pdo->prepare("SELECT p.*, i.image_url as image,
            (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'shipping_type') as shipping_type
            FROM catalog_product_entity p 
            LEFT JOIN catalog_product_image i ON p.entity_id = i.product_id AND i.is_main_image = true
            WHERE p.entity_id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            $item_total = $product['price'] * $quantity;
            $subtotal += $item_total;
            if ($product['shipping_type'] === 'freight') {
                $hasFreightItem = true;
            }
            
            $cartItems[$id] = [
                'id' => $product['entity_id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => (strpos($product['image'], 'http') === 0) ? $product['image'] : $product['image'],
                'stock_count' => $product['stock_count'],
                'quantity' => $quantity,
                'item_total' => $item_total
            ];
        } else {
            // Product no longer exists, remove from DB cart
            updateCartItemDb($pdo, $cartId, $id, 0);
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
$shipping_fee = calculate_shipping_cost($pdo, $defaultShippingMethod, $subtotal);

// Load View
require_once __DIR__ . '/../views/cart.view.php';
?>
