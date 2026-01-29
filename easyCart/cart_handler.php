<?php
session_start();
include 'data/data.php';
include 'utils/coupon_utils.php';
include 'utils/shipping_utils.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        $productId = $_POST['product_id'] ?? null;
        if (!$productId || !isset($products[$productId])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid product']);
            exit();
        }

        $availableStock = $products[$productId]['stock_count'] ?? 0;
        $currentQty = $_SESSION['cart'][$productId] ?? 0;
        $newQty = $currentQty + 1;

        if ($newQty <= $availableStock) {
            $_SESSION['cart'][$productId] = $newQty;
            echo json_encode([
                'status' => 'success',
                'message' => 'Product added to cart!',
                'cart_count' => array_sum($_SESSION['cart'] ?? [])
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Not enough stock available!']);
        }
        break;

    case 'update':
        $productId = $_POST['product_id'] ?? null;
        $qtyAction = $_POST['qty_action'] ?? ''; // 'plus' or 'minus'

        if (!$productId || !isset($products[$productId])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid product']);
            exit();
        }

        $availableStock = $products[$productId]['stock_count'] ?? 0;
        $currentQty = $_SESSION['cart'][$productId] ?? 0;

        if ($qtyAction === 'plus') {
            if ($currentQty < $availableStock) {
                $_SESSION['cart'][$productId]++;
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Max stock reached!']);
                exit();
            }
        } elseif ($qtyAction === 'minus') {
            $_SESSION['cart'][$productId]--;
            if ($_SESSION['cart'][$productId] < 1) {
                unset($_SESSION['cart'][$productId]);
            }
        }

        sendCartUpdates($products);
        break;

    case 'remove':
        $productId = $_POST['product_id'] ?? null;
        if ($productId && isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
        }
        sendCartUpdates($products);
        break;

    case 'calculate_shipping':
        $method = $_POST['shipping_method'] ?? $_SESSION['shipping_method'] ?? 'standard';
        $_SESSION['shipping_method'] = $method; // Persist in session
        $subtotal = (float)($_POST['subtotal'] ?? 0);
        $coupon_code = $_POST['coupon_code'] ?? '';

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
            echo json_encode(['status' => 'error', 'message' => implode("\n", $errors)]);
            exit();
        }
        // --- BACKEND VALIDATION END ---

        // 1. Clear the cart and shipping method
        unset($_SESSION['cart']);
        unset($_SESSION['shipping_method']);

        // 2. Set success message
        $coupon_code = $_POST['coupon_code'] ?? '';
        $coupon_text = '';
        if (!empty($coupon_code)) {
            $coupon_text = " (Coupon applied)";
        }

        $_SESSION['order_success'] = "Thank you, $name! Your order has been placed successfully$coupon_text.";

        echo json_encode(['status' => 'success', 'message' => 'Order placed successfully!']);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}

function sendCartUpdates($products)
{
    if (!isset($_SESSION['cart'])) {
        echo json_encode(['status' => 'error', 'message' => 'Cart is empty', 'cart_count' => 0]);
        exit();
    }

    $subtotal = 0;
    $items = [];
    foreach ($_SESSION['cart'] as $id => $quantity) {
        if (isset($products[$id])) {
            $item_total = $products[$id]['price'] * $quantity;
            $subtotal += $item_total;
            $items[$id] = [
                'quantity' => $quantity,
                'item_total' => '₹' . number_format($item_total),
                'is_maxed' => ($quantity >= $products[$id]['stock_count'])
            ];
        }
    }

    $shipping_fee = 40;
    $total = $subtotal > 0 ? ($subtotal + $shipping_fee) : 0;

    echo json_encode([
        'status' => 'success',
        'cart_count' => array_sum($_SESSION['cart']),
        'subtotal' => '₹' . number_format($subtotal),
        'shipping' => '₹' . number_format($subtotal > 0 ? $shipping_fee : 0),
        'total' => '₹' . number_format($total),
        'items' => $items
    ]);
}
