<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/src/init.php';

// Simulate Session
$_SESSION['user_id'] = 1;

// Fetch an existing cart
$stmt = $pdo->query("SELECT cart_id FROM sales_cart LIMIT 1");
$cartId = $stmt->fetchColumn();

if (!$cartId) {
    // Create cart
    $pdo->exec("INSERT INTO sales_cart (user_id, is_active, session_id) VALUES (1, 1, 'debug_session')");
    $cartId = $pdo->lastInsertId();
    echo "Created cart $cartId\n";
}
$_SESSION['cart_id'] = $cartId;

// Fetch a product in cart
$stmt = $pdo->prepare("SELECT product_id FROM sales_cart_product WHERE cart_id = ? LIMIT 1");
$stmt->execute([$cartId]);
$productId = $stmt->fetchColumn();

if (!$productId) {
    echo "No item in cart $cartId to update. Adding one...\n";
    // Get valid product
    $stmt = $pdo->query("SELECT entity_id FROM catalog_product_entity LIMIT 1");
    $pid = $stmt->fetchColumn();
    if ($pid) {
        $stmt = $pdo->prepare("INSERT INTO sales_cart_product (cart_id, product_id, quantity) VALUES (?, ?, 1)");
        $stmt->execute([$cartId, $pid]);
        $productId = $pid;
        echo "Added product $productId to cart.\n";
    } else {
        exit("No products in DB to test with.\n");
    }
}

echo "Testing Update on Cart: $cartId, Product: $productId\n";

$_POST['action'] = 'update';
$_POST['product_id'] = $productId;
$_POST['qty_action'] = 'plus';

// Invoke Controller
$GLOBALS['cartId'] = $cartId;
$GLOBALS['userId'] = 1;
$GLOBALS['pdo'] = $pdo;

$controller = new \App\Controller\Cart();
ob_start();
try {
    $controller->handlerAction();
} catch (Throwable $e) {
    echo "Caught Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
$output = ob_get_clean();

echo "--- OUTPUT ---\n";
echo $output . "\n";
echo "--- END ---\n";
