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
                'cart_count' => count($_SESSION['cart'])
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


    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}

function sendCartUpdates($products)
{
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        echo json_encode([
            'status' => 'success',
            'cart_count' => 0,
            'subtotal' => 0,
            'cart_html' => '<tr><td colspan="6" class="empty-msg">Your cart is empty.</td></tr>'
        ]);
        return;
    }

    $cartCount = count($_SESSION['cart']);
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
        'cart_count' => count($_SESSION['cart']),
        'subtotal' => '₹' . number_format($subtotal),
        'shipping' => '₹' . number_format($subtotal > 0 ? $shipping_fee : 0),
        'total' => '₹' . number_format($total),
        'items' => $items
    ]);
}
