<?php
require_once __DIR__ . '/../init.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 1. Fetch orders from Database
$userId = $_SESSION['user_id'];

if (!is_numeric($userId)) {
    header("Location: login.php");
    exit();
}

$userOrders = [];

$stmt = $pdo->prepare("SELECT o.*, 
    i.item_id, i.product_name_snapshot, i.price_snapshot, i.quantity,
    (SELECT image_url FROM catalog_product_image WHERE product_id = i.product_id AND is_main_image = true) as main_image
    FROM sales_order o 
    JOIN sales_order_item i ON o.order_id = i.order_id
    WHERE o.user_id = :u_id 
    ORDER BY o.created_at DESC");
$stmt->execute([':u_id' => $userId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group items by order
foreach ($rows as $row) {
    if (!isset($userOrders[$row['order_id']])) {
        $userOrders[$row['order_id']] = [
            'order_id' => $row['order_number'], // Using order_number for display
            'date' => $row['created_at'],
            'total' => $row['final_amount'],
            'status' => $row['status'],
            'shipping_method' => $row['shipping_cost'] > 0 ? 'white_glove' : 'standard', // Mocking method based on cost for display
            'items' => []
        ];
    }
    $userOrders[$row['order_id']]['items'][] = [
        'name' => $row['product_name_snapshot'],
        'price' => $row['price_snapshot'],
        'qty' => $row['quantity'],
        'image' => (strpos($row['main_image'], 'http') === 0) ? $row['main_image'] : $row['main_image']
    ];
}

// Page meta
$pageTitle = 'EasyCart | My Orders';
$currentPage = 'orders';
$extraStyles = ['orders.css'];
$extraScripts = [];

// Load View
require_once __DIR__ . '/../views/orders.view.php';
?>
