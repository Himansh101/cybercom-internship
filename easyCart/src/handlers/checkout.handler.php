<?php
require_once __DIR__ . '/../init.php';

// Guest checkout is allowed. Login check removed from top.
require_once __DIR__ . '/../utils/Validator.php';
use App\Utils\Validator;

$action = $_POST['action'] ?? '';
error_log("Checkout Handler Action: " . $action);
error_log("POST Data: " . print_r($_POST, true));

$userId = $_SESSION['user_id'] ?? null;
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
        $payment_method = $_POST['payment_method'] ?? $metadata['payment_method'] ?? 'cod';

        // Save to DB Metadata
        updateCartMetadata($pdo, $cartId, [
            'shipping_method' => $method,
            'coupon_code' => $coupon_code,
            'payment_method' => $payment_method
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

    case 'create_payment_intent':
        try {
            $cartItems = loadCartArrayFromDb($pdo, $cartId);
            if (empty($cartItems))
                throw new Exception("Cart is empty");

            // Calculate exact total again
            $subtotal = 0;
            $hasFreight = false;
            foreach ($cartItems as $id => $qty) {
                $stmt = $pdo->prepare("SELECT price, (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'shipping_type') as type FROM catalog_product_entity p WHERE entity_id = ?");
                $stmt->execute([$id]);
                $p = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($p) {
                    $subtotal += $p['price'] * $qty;
                    if (($p['type'] ?? '') === 'freight')
                        $hasFreight = true;
                }
            }

            $metadata = getCartMetadata($pdo, $cartId);
            $coupon = get_coupon_data($pdo, $metadata['coupon_code'] ?? '', $subtotal);
            $discountedSubtotal = $subtotal - $coupon['discount_amount'];

            $shippingMethod = $metadata['shipping_method'] ?? ($hasFreight || $subtotal > 300 ? 'white_glove' : 'standard');
            $shipping = calculate_shipping_cost($pdo, $shippingMethod, $discountedSubtotal);
            $gst = $discountedSubtotal * 0.18;
            $finalTotal = $discountedSubtotal + $shipping + $gst;

            // Stripe expects integer logic (e.g. paisa for INR)
            $amountInSmallestUnit = round($finalTotal * 100);

            $intent = stripe_create_payment_intent($amountInSmallestUnit, 'inr', ['user_id' => $userId, 'cart_id' => $cartId]);

            echo json_encode(['status' => 'success', 'clientSecret' => $intent['client_secret']]);

        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        break;

    case 'check_email':
        $email = trim($_POST['email'] ?? '');
        if (!Validator::email($email)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
            exit();
        }

        $stmt = $pdo->prepare("SELECT entity_id FROM customer_entity WHERE email = ?");
        $stmt->execute([$email]);
        $exists = $stmt->fetch();

        echo json_encode([
            'status' => 'success',
            'exists' => (bool) $exists,
            'message' => $exists ? 'Email already registered. Please login to continue.' : 'New email.'
        ]);
        break;

    case 'place_order':
        $errors = [];
        $dbCartItems = loadCartArrayFromDb($pdo, $cartId);
        if (empty($dbCartItems)) {
            $errors[] = "Your cart is empty.";
        }

        $shipping_name = trim($_POST['shipping_name'] ?? '');
        $shipping_mobile = trim($_POST['shipping_mobile'] ?? '');
        $shipping_email = trim($_POST['shipping_email'] ?? '');
        $shipping_address = trim($_POST['shipping_address'] ?? '');
        $shipping_city = trim($_POST['shipping_city'] ?? '');
        $shipping_pincode = trim($_POST['shipping_pincode'] ?? '');
        $paymentMethod = $_POST['payment_method'] ?? 'cod';
        $paymentIntentId = $_POST['payment_intent_id'] ?? null;

        // Using Validator
        if (!Validator::name($shipping_name))
            $errors[] = "Please enter a valid name (min 3 letters).";
        if (!Validator::phone($shipping_mobile))
            $errors[] = "Invalid mobile number. Use +91 format.";
        if (!Validator::email($shipping_email))
            $errors[] = "Invalid email address.";
        if (!Validator::address($shipping_address))
            $errors[] = "Address must be at least 10 characters.";
        if (!Validator::pincode($shipping_pincode))
            $errors[] = "Invalid 6-digit pincode.";

        if (!empty($errors)) {
            echo json_encode(['status' => 'error', 'message' => implode("\n", $errors)]);
            exit();
        }

        // Check if email belongs to existing user (Security for guest checkout)
        if (!$userId) {
            $stmt = $pdo->prepare("SELECT entity_id FROM customer_entity WHERE email = ?");
            $stmt->execute([$shipping_email]);
            if ($stmt->fetch()) {
                echo json_encode(['status' => 'error', 'message' => 'This email is already registered. Please login to place your order.']);
                exit();
            }
        }

        // Verify Stripe Payment if applicable
        $paymentStatus = 'pending';
        if ($paymentMethod === 'stripe') {
            if (!$paymentIntentId) {
                echo json_encode(['status' => 'error', 'message' => 'Missing payment information.']);
                exit();
            }
            try {
                $intent = stripe_retrieve_payment_intent($paymentIntentId);
                if (($intent['status'] ?? '') === 'succeeded') {
                    $paymentStatus = 'paid';
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Payment verification failed: ' . ($intent['status'] ?? 'Unknown')]);
                    exit();
                }
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => 'Payment error: ' . $e->getMessage()]);
                exit();
            }
        }

        try {
            $pdo->beginTransaction();

            $orderNumber = strtoupper(substr(uniqid('ORD'), -8));

            // 1. Calculate totals again securely
            $subtotal = 0;
            $itemsToProcess = [];
            $cartItems = loadCartArrayFromDb($pdo, $cartId);

            if (empty($cartItems)) {
                throw new Exception("Your cart is empty.");
            }

            foreach ($cartItems as $pid => $quantity) {
                $stmt = $pdo->prepare("SELECT entity_id, name, price, stock_count FROM catalog_product_entity WHERE entity_id = ?");
                $stmt->execute([$pid]);
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
            $stmt = $pdo->prepare("INSERT INTO sales_order (user_id, order_number, subtotal, shipping_cost, tax_amount, final_amount, status, created_at, payment_method, transaction_id, payment_status) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, ?, ?, ?) RETURNING order_id");
            $stmt->execute([$userId, $orderNumber, $discounted_subtotal, $shipping, $gst, $finalTotal, 'placed', $paymentMethod, $paymentIntentId, $paymentStatus]);
            $orderId = $stmt->fetchColumn();

            // 3. Insert Items & Update Stock
            $stmtItem = $pdo->prepare("INSERT INTO sales_order_item (order_id, product_id, product_name_snapshot, price_snapshot, quantity) VALUES (?, ?, ?, ?, ?)");
            $stmtStock = $pdo->prepare("UPDATE catalog_product_entity SET stock_count = stock_count - ? WHERE entity_id = ? RETURNING stock_count");
            $stmtAttr = $pdo->prepare("UPDATE catalog_product_attribute SET attribute_value = ? WHERE entity_id = ? AND attribute_key = 'in_stock'");

            foreach ($itemsToProcess as $item) {
                $stmtItem->execute([$orderId, $item['id'], $item['name'], $item['price'], $item['qty']]);

                $stmtStock->execute([$item['qty'], $item['id']]);
                $newStock = $stmtStock->fetchColumn();

                if ($newStock <= 0) {
                    $stmtAttr->execute(['0', $item['id']]);
                }
            }

            // 4. Insert Addresses
            $stmtAddr = $pdo->prepare("INSERT INTO sales_order_address (order_id, full_name, email, mobile, street_address, city, pincode, address_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtAddr->execute([$orderId, $shipping_name, $shipping_email, $shipping_mobile, $shipping_address, $shipping_city, $shipping_pincode, 'shipping']);

            if (isset($_POST['billing_same_as_shipping']) && $_POST['billing_same_as_shipping'] == '1') {
                $stmtAddr->execute([$orderId, $shipping_name, $shipping_email, $shipping_mobile, $shipping_address, $shipping_city, $shipping_pincode, 'billing']);
            } else {
                $billing_name = trim($_POST['billing_name'] ?? '');
                $billing_address = trim($_POST['billing_address'] ?? '');
                $billing_city = trim($_POST['billing_city'] ?? '');
                $billing_pincode = trim($_POST['billing_pincode'] ?? '');
                $stmtAddr->execute([$orderId, $billing_name, $shipping_email, $shipping_mobile, $billing_address, $billing_city, $billing_pincode, 'billing']);
            }

            // 5. Deactivate Cart
            if (isset($cartId) && $cartId) {
                $pdo->prepare("UPDATE sales_cart SET is_active = FALSE WHERE cart_id = ?")->execute([$cartId]);
                $pdo->prepare("DELETE FROM sales_cart_product WHERE cart_id = ?")->execute([$cartId]);
            }

            $pdo->commit();

            // 6. Save Address to User Profile if logged in
            if ($userId) {
                $stmtSaveAddr = $pdo->prepare("UPDATE customer_entity SET street_address = ?, city = ?, pincode = ? WHERE entity_id = ?");
                $stmtSaveAddr->execute([$shipping_address, $shipping_city, $shipping_pincode, $userId]);
            }

            unset($_SESSION['cart']);
            unset($_SESSION['cart_id']);
            clearCartMetadata($pdo, $cartId);

            echo json_encode(['status' => 'success', 'message' => 'Order placed successfully!', 'order_id' => $orderNumber]);

        } catch (Exception $e) {
            if ($pdo->inTransaction())
                $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Failed to place order: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
?>