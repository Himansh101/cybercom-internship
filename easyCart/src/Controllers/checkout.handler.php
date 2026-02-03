<?php
require_once __DIR__ . '/../init.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$action = $_POST['action'] ?? '';
global $products;

// Ensure is_ajax is detected
if (isset($_POST['is_ajax'])) {
    header('Content-Type: application/json');
}

switch ($action) {
    case 'calculate_shipping':
        $subtotal = 0;
        $hasFreightItem = false;

        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $id => $quantity) {
                if (isset($products[$id])) {
                    $subtotal += $products[$id]['price'] * $quantity;
                    if (isset($products[$id]['item_shipping_type']) && $products[$id]['item_shipping_type'] === 'freight') {
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

        $method = $_POST['shipping_method'] ?? $_SESSION['shipping_method'] ?? 'standard';
        if (!in_array($method, $allowedShippingMethods)) {
            $method = $allowedShippingMethods[0];
        }

        $_SESSION['shipping_method'] = $method;

        $coupon_code = $_POST['coupon_code'] ?? '';
        $_SESSION['coupon_code'] = $coupon_code;

        $coupon_data = get_coupon_data($coupon_code, $subtotal);
        $discount = $coupon_data['discount_amount'];
        $discount_pct = $coupon_data['discount_pct'];
        $coupon_valid = $coupon_data['valid'];
        $coupon_message = $coupon_data['message'];

        $discounted_subtotal = $subtotal - $discount;
        $shipping = calculate_shipping_cost($method, $discounted_subtotal);
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
        unset($_SESSION['coupon_code']);
        echo json_encode(['status' => 'success', 'message' => 'Coupon removed']);
        break;

    case 'place_order':
        $errors = [];
        if (empty($_SESSION['cart'])) {
            $errors[] = "Your cart is empty.";
        }

        $name = trim($_POST['name'] ?? '');
        if (strlen($name) < 3 || !preg_match("/^[a-zA-Z\s]+$/", $name)) {
            $errors[] = "Invalid name. Must be at least 3 characters and contain only letters.";
        }

        $mobile = trim($_POST['mobile'] ?? '');
        if (!preg_match("/^(\+91)[6-9][0-9]{9}$/", $mobile)) {
            $errors[] = "Invalid mobile number. Must start with +91 and contain 10 digits.";
        }

        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email address.";
        }

        $address = trim($_POST['address'] ?? '');
        if (strlen($address) < 10) {
            $errors[] = "Address is too short. Please provide at least 10 characters.";
        }

        $city = trim($_POST['city'] ?? '');
        if (strlen($city) < 2) {
            $errors[] = "Invalid city name.";
        }

        $pincode = trim($_POST['pincode'] ?? '');
        if (!preg_match("/^[1-9][0-9]{5}$/", $pincode)) {
            $errors[] = "Invalid Pincode. Must be a 6-digit number.";
        }

        if (!empty($errors)) {
            if (isset($_POST['is_ajax'])) {
                echo json_encode(['status' => 'error', 'message' => implode("\n", $errors)]);
            } else {
                $_SESSION['checkout_errors'] = implode("\n", $errors);
                header("Location: checkout.php");
            }
            exit();
        }

        // Calculate Order Details for saving
        $orderItems = [];
        $totalAmt = 0;
        foreach ($_SESSION['cart'] as $id => $quantity) {
            if (isset($products[$id])) {
                $orderItems[] = [
                    'id' => $id,
                    'qty' => $quantity,
                    'price' => $products[$id]['price']
                ];
                $totalAmt += $products[$id]['price'] * $quantity;
            }
        }

        // Apply shipping and taxes (simplified calculation to match checkout.controller)
        $shipping = $_SESSION['shipping_cost'] ?? 0;
        $finalTotal = $totalAmt + $shipping + ($totalAmt * 0.18); // Including GST

        $newOrder = [
            'order_id' => strtoupper(substr(uniqid('ORD'), -8)),
            'date' => date('Y-m-d H:i:s'),
            'items' => $orderItems,
            'total' => $finalTotal,
            'shipping_method' => $_SESSION['shipping_method'] ?? 'standard',
            'status' => 'placed',
            'address' => [
                'name' => $name,
                'email' => $email,
                'mobile' => $mobile,
                'address' => $address,
                'city' => $city,
                'pincode' => $pincode
            ]
        ];

        if (isset($_SESSION['user']['id'])) {
            $userId = $_SESSION['user']['id'];
            $file = __DIR__ . '/../../users.json';
            if (file_exists($file)) {
                $users = json_decode(file_get_contents($file), true) ?? [];
                foreach ($users as &$u) {
                    if ($u['id'] === $userId) {
                        $u['cart'] = [];
                        if (!isset($u['orders'])) $u['orders'] = [];
                        $u['orders'][] = $newOrder;
                        break;
                    }
                }
                file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));
            }
        }

        unset($_SESSION['cart']);
        unset($_SESSION['shipping_method']);
        unset($_SESSION['shipping_cost']);
        unset($_SESSION['coupon_code']);

        $coupon_code = $_POST['coupon_code'] ?? '';
        $coupon_text = !empty($coupon_code) ? " (Coupon applied)" : "";
        $_SESSION['order_success'] = "Thank you, $name! Your order has been placed successfully$coupon_text.";

        if (isset($_POST['is_ajax']) || isset($_GET['is_ajax'])) {
            echo json_encode(['status' => 'success', 'message' => 'Order placed successfully!']);
        } else {
            header("Location: orders.php");
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
?>
