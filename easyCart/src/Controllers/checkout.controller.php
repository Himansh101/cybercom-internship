<?php
require_once __DIR__ . '/../init.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

global $products;

// Page meta
$pageTitle = 'EasyCart | Checkout';
$currentPage = 'checkout';
$extraStyles = ['cart.css'];
$extraScripts = ['checkout.js'];

// Redirect to product page if cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: plp.php");
    exit();
}

// 1. Calculate Subtotal first and determine allowed shipping methods
$subtotal = 0;
$hasFreightItem = false;

foreach ($_SESSION['cart'] as $id => $quantity) {
    if (isset($products[$id])) {
        $subtotal += $products[$id]['price'] * $quantity;

        // Check if this product requires freight shipping
        if (isset($products[$id]['item_shipping_type']) && $products[$id]['item_shipping_type'] === 'freight') {
            $hasFreightItem = true;
        }
    }
}

// Determine allowed shipping methods
if ($hasFreightItem || $subtotal > 300) {
    $allowedShippingMethods = ['white_glove', 'freight'];
} else {
    $allowedShippingMethods = ['standard', 'express'];
}

// 2. Apply Coupon Discount (if valid)
$coupon_code = $_POST['coupon_code'] ?? $_SESSION['coupon_code'] ?? '';
$coupon_data = get_coupon_data($coupon_code, $subtotal);

$discount = $coupon_data['discount_amount'];
$discount_percentage = $coupon_data['discount_pct'];
$discount_message = $coupon_data['message'];

$discounted_subtotal = $subtotal - $discount;

// 3. Determine Shipping Cost based on button click or radio selection
$method = $_POST['shipping_method'] ?? $_SESSION['shipping_method'] ?? 'standard';

// Validate that selected method is allowed
if (!in_array($method, $allowedShippingMethods)) {
    $method = $allowedShippingMethods[0];
}

$_SESSION['shipping_method'] = $method;
$shipping = calculate_shipping_cost($method, $discounted_subtotal);

// 4. Calculate GST (18%)
$gst_rate = 0.18;
$gst = $discounted_subtotal * $gst_rate;

// 5. Final Total
$final_total = $discounted_subtotal + $shipping + $gst;

// Load View
require_once __DIR__ . '/../views/checkout.view.php';
?>
