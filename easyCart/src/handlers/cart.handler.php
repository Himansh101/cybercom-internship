<?php
require_once __DIR__ . '/../init.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        $productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : null;
        if (!$productId) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid product']);
            exit();
        }

        $stmt = $pdo->prepare("SELECT stock_count FROM catalog_product_entity WHERE entity_id = ?");
        $stmt->execute([$productId]);
        $availableStock = $stmt->fetchColumn();

        if ($availableStock === false) {
            echo json_encode(['status' => 'error', 'message' => 'Product not found']);
            exit();
        }

        // Ensure we have a cart_id (Create if missing)
        if (!$cartId) {
            $cartId = getOrCreateCartId($pdo, $userId, true);
        }

        // Get current quantity from DB
        $stmt = $pdo->prepare("SELECT quantity FROM sales_cart_product WHERE cart_id = ? AND product_id = ?");
        $stmt->execute([$cartId, $productId]);
        $currentQty = (int) $stmt->fetchColumn();

        $newQty = $currentQty + 1;

        if ($newQty <= $availableStock) {
            updateCartItemDb($pdo, $cartId, $productId, $newQty);
            sendCartUpdates($pdo);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Not enough stock available!']);
        }
        break;

    case 'update':
        $productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : null;
        $qtyAction = $_POST['qty_action'] ?? '';

        if (!$productId || !$cartId) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
            exit();
        }

        $stmt = $pdo->prepare("SELECT stock_count FROM catalog_product_entity WHERE entity_id = ?");
        $stmt->execute([$productId]);
        $availableStock = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT quantity FROM sales_cart_product WHERE cart_id = ? AND product_id = ?");
        $stmt->execute([$cartId, $productId]);
        $currentQty = (int) $stmt->fetchColumn();

        if ($qtyAction === 'plus') {
            if ($currentQty < $availableStock) {
                $currentQty++;
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Max stock reached!']);
                exit();
            }
        } elseif ($qtyAction === 'minus') {
            $currentQty--;
        }

        updateCartItemDb($pdo, $cartId, $productId, $currentQty);
        sendCartUpdates($pdo);
        break;

    case 'remove':
        $productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : null;
        if ($productId && $cartId) {
            updateCartItemDb($pdo, $cartId, $productId, 0); // 0 quantity deletes the record
        }
        sendCartUpdates($pdo);
        break;

    case 'restore':
        $localCart = $_POST['cart_data'] ?? [];
        if (!empty($localCart) && is_array($localCart) && $cartId) {
            foreach ($localCart as $pid => $qty) {
                $pid = (int) $pid;
                $stmt = $pdo->prepare("SELECT entity_id FROM catalog_product_entity WHERE entity_id = ?");
                $stmt->execute([$pid]);
                if ($stmt->fetch()) {
                    updateCartItemDb($pdo, $cartId, $pid, (int) $qty);
                }
            }
            echo json_encode(['status' => 'success', 'message' => 'Cart restored']);
        } else {
            echo json_encode(['status' => 'success', 'message' => 'Nothing to restore']);
        }
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}


function sendCartUpdates($pdo)
{
    global $cartId;
    $cartItems = loadCartArrayFromDb($pdo, $cartId);

    if (empty($cartItems)) {
        echo json_encode([
            'status' => 'success',
            'cart_count' => 0,
            'cart_data' => [],
            'subtotal' => 0,
            'cart_html' => '<tr><td colspan="6" class="empty-msg">Your cart is empty.</td></tr>'
        ]);
        return;
    }

    $cartCount = count($cartItems);
    $subtotal = 0;
    $hasFreightItem = false;
    $items = [];

    foreach ($cartItems as $id => $quantity) {
        $stmt = $pdo->prepare("SELECT p.name, p.price, p.stock_count, 
            (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'shipping_type') as shipping_type
            FROM catalog_product_entity p WHERE p.entity_id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            $item_total = $product['price'] * $quantity;
            $subtotal += $item_total;

            if ($product['shipping_type'] === 'freight') {
                $hasFreightItem = true;
            }

            $items[$id] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $quantity,
                'item_total' => '₹' . number_format($item_total),
                'is_maxed' => ($quantity >= $product['stock_count'])
            ];
        }
    }

    $shippingMethod = ($hasFreightItem || $subtotal > 300) ? 'white_glove' : 'standard';
    $shipping_fee = calculate_shipping_cost($pdo, $shippingMethod, $subtotal);
    $total = $subtotal > 0 ? ($subtotal + $shipping_fee) : 0;

    $methodNames = [
        'standard' => 'Standard',
        'express' => 'Express',
        'freight' => 'Freight',
        'white_glove' => 'White Glove'
    ];

    echo json_encode([
        'status' => 'success',
        'cart_count' => count($cartItems),
        'cart_data' => $cartItems,
        'subtotal' => '₹' . number_format($subtotal),
        'shipping' => $methodNames[$shippingMethod] . ' - ₹' . number_format($subtotal > 0 ? $shipping_fee : 0),
        'shipping_method' => $shippingMethod,
        'total' => '₹' . number_format($total),
        'items' => $items
    ]);
}
?>