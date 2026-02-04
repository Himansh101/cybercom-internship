<?php
require_once __DIR__ . '/../init.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$action = $_POST['action'] ?? '';
$userId = $_SESSION['user_id'];
global $products;

// Ensure is_ajax is detected
if (isset($_POST['is_ajax'])) {
    header('Content-Type: application/json');
}

switch ($action) {
    case 'calculate_shipping':
        $subtotal = 0;
        $hasFreightItem = false;

        $cartItems = loadCartArrayFromDb($pdo, $cartId);

        if (!empty($cartItems)) {
            foreach ($cartItems as $id => $quantity) {
                $stmt = $pdo->prepare("SELECT price, 
                    (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'shipping_type') as shipping_type
                    FROM catalog_product_entity p WHERE p.entity_id = ?");
                $stmt->execute([$id]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($product) {
                    $subtotal += $product['price'] * $quantity;
                    if ($product['shipping_type'] === 'freight') {
                        $hasFreightItem = true;
                    }
                }
            }
        }

        if ($hasFreightItem || $subtotal > 300) {
            $allowedShippingMethods = ['white_glove', 'freight'];
            $shippingInfoMessage = ($hasFreightItem) ? 'Your cart contains freight items. Only premium shipping options are available.' : 'High-value cart (>₹300). Only premium shipping options are available.';
        } else {
            $allowedShippingMethods = ['standard', 'express'];
            $shippingInfoMessage = 'Standard shipping options available for your cart.';
        }

        $metadata = getCartMetadata($pdo, $cartId);
        $method = $_POST['shipping_method'] ?? $metadata['shipping_method'] ?? 'standard';
        
        if (!in_array($method, $allowedShippingMethods)) {
            $method = $allowedShippingMethods[0];
        }

        $coupon_code = $_POST['coupon_code'] ?? $metadata['coupon_code'] ?? '';
        
        // Save to DB Metadata
        updateCartMetadata($pdo, $cartId, [
            'shipping_method' => $method,
            'coupon_code' => $coupon_code
        ]);

        $coupon_data = get_coupon_data($pdo, $coupon_code, $subtotal);
        $discount = $coupon_data['discount_amount'];
        $discount_pct = $coupon_data['discount_pct'];
        $coupon_valid = $coupon_data['valid'];
        $coupon_message = $coupon_data['message'];

        $discounted_subtotal = $subtotal - $discount;
        $shipping = calculate_shipping_cost($pdo, $method, $discounted_subtotal);
        $gst = $discounted_subtotal * 0.18;
        $final_total = $discounted_subtotal + $shipping + $gst;

        echo json_encode([
            'status' => 'success',
            'subtotal_raw' => $subtotal,
            'has_freight_item' => $hasFreightItem,
            'allowed_methods' => $allowedShippingMethods,
            'shipping_info_message' => $shippingInfoMessage,
            'selected_method' => $method,
            'discount' => $discount,
            'discount_pct' => $discount_pct,
            'coupon_valid' => $coupon_valid,
            'coupon_message' => $coupon_message,
            'shipping' => $shipping,
            'gst' => $gst,
            'final_total' => $final_total,
            'discount_formatted' => number_format($discount),
            'shipping_formatted' => number_format($shipping),
            'gst_formatted' => number_format($gst),
            'total_formatted' => number_format($final_total)
        ]);
        break;

    case 'remove_coupon':
        $metadata = getCartMetadata($pdo, $cartId);
        updateCartMetadata($pdo, $cartId, [
            'shipping_method' => $metadata['shipping_method'],
            'coupon_code' => null
        ]);
        echo json_encode(['status' => 'success', 'message' => 'Coupon removed']);
        break;

    case 'place_order':
        $errors = [];
        $dbCartItems = loadCartArrayFromDb($pdo, $cartId);
        if (empty($dbCartItems)) {
            $errors[] = "Your cart is empty.";
        }

        $name = trim($_POST['name'] ?? '');
        $mobile = trim($_POST['mobile'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $pincode = trim($_POST['pincode'] ?? '');

        // (Validations kept as is for brevity, assume they pass for logic core)
        if (strlen($name) < 3) $errors[] = "Invalid name.";
        if (!preg_match("/^(\+91)[6-9][0-9]{9}$/", $mobile)) $errors[] = "Invalid mobile.";
        if (strlen($address) < 10) $errors[] = "Address too short.";

        if (!empty($errors)) {
            echo json_encode(['status' => 'error', 'message' => implode("\n", $errors)]);
            exit();
        }

        try {
            $pdo->beginTransaction();

            $userId = $_SESSION['user_id'];
            $orderNumber = strtoupper(substr(uniqid('ORD'), -8));
            
            // 1. Calculate totals again securely
            $subtotal = 0;
            $itemsToProcess = [];
            $cartItems = loadCartArrayFromDb($pdo, $cartId);

            if (empty($cartItems)) {
                throw new Exception("Your cart is empty.");
            }

            foreach ($cartItems as $id => $quantity) {
                $stmt = $pdo->prepare("SELECT entity_id, name, price, stock_count FROM catalog_product_entity WHERE entity_id = ?");
                $stmt->execute([$id]);
                $p = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($p) {
                    $subtotal += $p['price'] * $quantity;
                    $itemsToProcess[] = [
                        'id' => $p['entity_id'],
                        'name' => $p['name'],
                        'price' => $p['price'],
                        'qty' => $quantity
                    ];
                }
            }

            $metadata = getCartMetadata($pdo, $cartId);
            $coupon_data = get_coupon_data($pdo, $metadata['coupon_code'] ?? '', $subtotal);
            $discount = $coupon_data['discount_amount'];
            $discounted_subtotal = $subtotal - $discount;
            $shipping = calculate_shipping_cost($pdo, $metadata['shipping_method'] ?? 'standard', $discounted_subtotal);
            $gst = $discounted_subtotal * 0.18;
            $finalTotal = $discounted_subtotal + $shipping + $gst;

            // 2. Insert Order
            $stmt = $pdo->prepare("INSERT INTO sales_order (user_id, order_number, subtotal, shipping_cost, tax_amount, final_amount, status, created_at) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP) RETURNING order_id");
            $stmt->execute([$userId, $orderNumber, $discounted_subtotal, $shipping, $gst, $finalTotal, 'placed']);
            $orderId = $stmt->fetchColumn();

            // 3. Insert Items & Update Stock
            $stmtItem = $pdo->prepare("INSERT INTO sales_order_item (order_id, product_id, product_name_snapshot, price_snapshot, quantity) VALUES (?, ?, ?, ?, ?)");
            $stmtStock = $pdo->prepare("UPDATE catalog_product_entity SET stock_count = stock_count - ? WHERE entity_id = ? RETURNING stock_count");
            $stmtAttr = $pdo->prepare("UPDATE catalog_product_attribute SET attribute_value = ? WHERE entity_id = ? AND attribute_key = 'in_stock'");

            foreach ($itemsToProcess as $item) {
                $stmtItem->execute([$orderId, $item['id'], $item['name'], $item['price'], $item['qty']]);
                
                $stmtStock->execute([$item['qty'], $item['id']]);
                $newStock = $stmtStock->fetchColumn();

                // If stock hits 0, update attribute to '0'
                if ($newStock <= 0) {
                    $stmtAttr->execute(['0', $item['id']]);
                }
            }

            // 4. Insert Address
            $stmtAddr = $pdo->prepare("INSERT INTO sales_order_address (order_id, full_name, street_address, city, pincode) VALUES (?, ?, ?, ?, ?)");
            $stmtAddr->execute([$orderId, $name, $address, $city, $pincode]);

            // 5. Deactivate Cart & Clear Database Cart Items
            if (isset($cartId) && $cartId) {
                $pdo->prepare("UPDATE sales_cart SET is_active = FALSE WHERE cart_id = ?")->execute([$cartId]);
                $pdo->prepare("DELETE FROM sales_cart_product WHERE cart_id = ?")->execute([$cartId]);
            }

            $pdo->commit();

            unset($_SESSION['cart']);
            unset($_SESSION['cart_id']);
            clearCartMetadata($pdo, $cartId);

            echo json_encode(['status' => 'success', 'message' => 'Order placed successfully!', 'order_id' => $orderNumber]);

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Failed to place order: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
?>
