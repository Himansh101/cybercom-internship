<?php
session_start();
include 'data/data.php';
include 'utils/coupon_utils.php';
include 'utils/shipping_utils.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$action = $_POST['action'] ?? '';

// DEBUG: Log request data
// file_put_contents('debug_checkout.log', print_r($_POST, true), FILE_APPEND);

// Ensure is_ajax is detected
if (isset($_POST['is_ajax'])) {
    // Force JSON header
    header('Content-Type: application/json');
}

switch ($action) {
    case 'calculate_shipping':
        // Calculate subtotal and check for freight items from session cart
        $subtotal = 0;
        $hasFreightItem = false;

        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
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

        // Determine allowed shipping methods based on cart contents and subtotal
        if ($hasFreightItem) {
            $allowedShippingMethods = ['white_glove', 'freight'];
            $shippingInfoMessage = 'Your cart contains freight items. Only premium shipping options are available.';
        } elseif ($subtotal > 300) {
            $allowedShippingMethods = ['white_glove', 'freight'];
            $shippingInfoMessage = 'High-value cart (>₹300). Only premium shipping options are available.';
        } else {
            $allowedShippingMethods = ['standard', 'express'];
            $shippingInfoMessage = 'Standard shipping options available for your cart.';
        }

        $method = $_POST['shipping_method'] ?? $_SESSION['shipping_method'] ?? 'standard';

        // Validate that selected method is allowed
        if (!in_array($method, $allowedShippingMethods)) {
            // Auto-select first allowed method
            $method = $allowedShippingMethods[0];
        }

        $_SESSION['shipping_method'] = $method; // Persist in session

        $coupon_code = $_POST['coupon_code'] ?? '';
        $_SESSION['coupon_code'] = $coupon_code; // Persist in session

        // Use centralized coupon logic
        $coupon_data = get_coupon_data($coupon_code, $subtotal);
        $discount = $coupon_data['discount_amount'];
        $discount_pct = $coupon_data['discount_pct'];
        $coupon_valid = $coupon_data['valid'];
        $coupon_message = $coupon_data['message'];

        $discounted_subtotal = $subtotal - $discount;

        // Shipping Calculation
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
        // --- BACKEND VALIDATION START ---
        $errors = [];

        // 1. Validate Cart
        if (empty($_SESSION['cart'])) {
            $errors[] = "Your cart is empty.";
        }

        // 2. Validate Name
        $name = trim($_POST['name'] ?? '');
        if (strlen($name) < 3 || !preg_match("/^[a-zA-Z\s]+$/", $name)) {
            $errors[] = "Invalid name. Must be at least 3 characters and contain only letters.";
        }

        // 3. Validate Mobile (+91 followed by 10 digits starting with 6-9)
        $mobile = trim($_POST['mobile'] ?? '');
        if (!preg_match("/^(\+91)[6-9][0-9]{9}$/", $mobile)) {
            $errors[] = "Invalid mobile number. Must start with +91 and contain 10 digits.";
        }

        // 4. Validate Email
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email address.";
        }

        // 5. Validate Address
        $address = trim($_POST['address'] ?? '');
        if (strlen($address) < 10) {
            $errors[] = "Address is too short. Please provide at least 10 characters.";
        }

        // 6. Validate City
        $city = trim($_POST['city'] ?? '');
        if (strlen($city) < 2) {
            $errors[] = "Invalid city name.";
        }

        // 7. Validate Pincode (6 digits)
        $pincode = trim($_POST['pincode'] ?? '');
        if (!preg_match("/^[1-9][0-9]{5}$/", $pincode)) {
            $errors[] = "Invalid Pincode. Must be a 6-digit number.";
        }

        // Return errors if any
        if (!empty($errors)) {
            if (isset($_POST['is_ajax'])) {
                echo json_encode(['status' => 'error', 'message' => implode("\n", $errors)]);
            } else {
                $_SESSION['checkout_errors'] = implode("\n", $errors);
                session_write_close(); // Ensure session is saved before redirect
                header("Location: checkout.php");
            }
            exit();
        }
        // --- BACKEND VALIDATION END ---

        // 1. Clear the cart and shipping method
        unset($_SESSION['cart']);
        unset($_SESSION['shipping_method']);
        unset($_SESSION['coupon_code']);

        // Sync empty cart to users.json for logged-in users
        if (isset($_SESSION['user']['id'])) {
            $userId = $_SESSION['user']['id'];
            $file = 'users.json';
            if (file_exists($file)) {
                $users = json_decode(file_get_contents($file), true) ?? [];
                foreach ($users as &$u) {
                    if ($u['id'] === $userId) {
                        $u['cart'] = []; // Clear persistent cart
                        break;
                    }
                }
                file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));
            }
        }

        // 2. Set success message
        $coupon_code = $_POST['coupon_code'] ?? '';
        $coupon_text = '';
        if (!empty($coupon_code)) {
            $coupon_text = " (Coupon applied)";
        }

        $_SESSION['order_success'] = "Thank you, $name! Your order has been placed successfully$coupon_text.";

        if (isset($_POST['is_ajax'])) {
            echo json_encode(['status' => 'success', 'message' => 'Order placed successfully!']);
        } else {
            header("Location: orders.php");
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
