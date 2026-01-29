<?php
session_start();
include 'data/data.php';
include 'utils/coupon_utils.php';
include 'utils/shipping_utils.php';

header('Content-Type: application/json');


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
            syncCartToJson(); // Sync
            echo json_encode([
                'status' => 'success',
                'message' => 'Product added to cart!',
                'cart_count' => count($_SESSION['cart']),
                'cart_data' => $_SESSION['cart']
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

        syncCartToJson(); // Sync
        sendCartUpdates($products);
        break;

    case 'remove':
        $productId = $_POST['product_id'] ?? null;
        if ($productId && isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
        }
        syncCartToJson(); // Sync
        sendCartUpdates($products);
        break;

    case 'restore':
        $localCart = $_POST['cart_data'] ?? []; // Associative array [id => qty]
        if (!empty($localCart) && is_array($localCart)) {
            // Validate items against product list
            foreach ($localCart as $pid => $qty) {
                if (isset($products[$pid])) {
                    $qty = (int)$qty;
                    if ($qty > 0) {
                        $_SESSION['cart'][$pid] = $qty; // Trust client qty? Or cap at stock? 
                        // For now trust, user can adjust later.
                    }
                }
            }
            syncCartToJson();
            echo json_encode(['status' => 'success', 'message' => 'Cart restored']);
        } else {
            echo json_encode(['status' => 'success', 'message' => 'Nothing to restore']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}

function syncCartToJson()
{
    if (!isset($_SESSION['user']['id'])) return;

    $userId = $_SESSION['user']['id'];
    $cart = $_SESSION['cart'] ?? [];

    $file = 'users.json';
    if (file_exists($file)) {
        $users = json_decode(file_get_contents($file), true) ?? [];
        foreach ($users as &$user) {
            if ($user['id'] === $userId) {
                $user['cart'] = $cart;
                break;
            }
        }
        file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));
    }
}

function sendCartUpdates($products)
{
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        echo json_encode([
            'status' => 'success',
            'cart_count' => 0,
            'cart_data' => [], // Empty cart
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
        'cart_data' => $_SESSION['cart'], // Send raw cart for LocalStorage
        'subtotal' => '₹' . number_format($subtotal),
        'shipping' => '₹' . number_format($subtotal > 0 ? $shipping_fee : 0),
        'total' => '₹' . number_format($total),
        'items' => $items
    ]);
}
