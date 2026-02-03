<?php
require_once __DIR__ . '/../init.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login");
    exit();
}

// Page meta
$pageTitle = 'EasyCart | Checkout';
$currentPage = 'checkout';
$extraStyles = ['cart.css'];
$extraScripts = ['checkout.js'];

// 1. Fetch Cart Items from Database
$dbCartItems = loadCartArrayFromDb($pdo, $cartId);

// Redirect to product page if cart is empty
if (empty($dbCartItems)) {
    header("Location: plp");
    exit();
}

$subtotal = 0;
$hasFreightItem = false;
$checkoutItems = [];

foreach ($dbCartItems as $id => $quantity) {
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

        $checkoutItems[$id] = [
            'name' => $product['name'],
            'price' => $product['price'],
            'image' => (strpos($product['image'] ?? '', 'http') === 0) ? $product['image'] : 'assets/' . $product['image'],
            'quantity' => $quantity,
            'item_total' => $item_total
        ];
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
