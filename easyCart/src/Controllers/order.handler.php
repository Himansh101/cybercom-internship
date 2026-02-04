<?php
require_once __DIR__ . '/../init.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'get_details') {
    $orderNumber = $_POST['order_id'] ?? '';
    $userId = $_SESSION['user_id'];

    if (!$orderNumber) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid Order ID']);
        exit();
    }

    try {
        // 1. Fetch Order Summary
        $stmtOrder = $pdo->prepare("SELECT * FROM sales_order WHERE order_number = ? AND user_id = ?");
        $stmtOrder->execute([$orderNumber, $userId]);
        $order = $stmtOrder->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            echo json_encode(['status' => 'error', 'message' => 'Order not found']);
            exit();
        }

        // 2. Fetch Order Address
        $stmtAddr = $pdo->prepare("SELECT * FROM sales_order_address WHERE order_id = ?");
        $stmtAddr->execute([$order['order_id']]);
        $address = $stmtAddr->fetch(PDO::FETCH_ASSOC);

        // 3. Fetch Order Items
        $stmtItems = $pdo->prepare("SELECT i.*, 
            (SELECT image_url FROM catalog_product_image WHERE product_id = i.product_id AND is_main_image = true) as main_image
            FROM sales_order_item i WHERE i.order_id = ?");
        $stmtItems->execute([$order['order_id']]);
        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        $formattedItems = [];
        foreach ($items as $item) {
            $formattedItems[] = [
                'name' => $item['product_name_snapshot'],
                'price' => (float)$item['price_snapshot'],
                'qty' => (int)$item['quantity'],
                'total' => $item['price_snapshot'] * $item['quantity'],
                'image' => (strpos($item['main_image'], 'http') === 0) ? $item['main_image'] : 'assets/' . $item['main_image']
            ];
        }

        echo json_encode([
            'status' => 'success',
            'data' => [
                'order_number' => $order['order_number'],
                'date' => date('d M, Y', strtotime($order['created_at'])),
                'status' => ucfirst($order['status']),
                'subtotal' => (float)$order['subtotal'],
                'shipping' => (float)$order['shipping_cost'],
                'tax' => (float)$order['tax_amount'],
                'total' => (float)$order['final_amount'],
                'address' => $address,
                'items' => $formattedItems
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
