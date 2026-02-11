<?php
// Silent debug script to capture JSON output or errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/src/init.php';

// Setup Mock Environment
$_SESSION['user_id'] = 1;
// Fetch existing cart
$cartId = $pdo->query("SELECT cart_id FROM sales_cart LIMIT 1")->fetchColumn();
if (!$cartId) {
    // Create
    $pdo->exec("INSERT INTO sales_cart (user_id, is_active, session_id) VALUES (1, 1, 'debug')");
    $cartId = $pdo->lastInsertId();
}
$_SESSION['cart_id'] = $cartId;
$GLOBALS['cartId'] = $cartId;
$GLOBALS['userId'] = 1;
$GLOBALS['pdo'] = $pdo;

// Ensure Item
$stmt = $pdo->prepare("SELECT product_id FROM sales_cart_product WHERE cart_id = ? LIMIT 1");
$stmt->execute([$cartId]);
$productId = $stmt->fetchColumn();
if (!$productId) {
    $pid = $pdo->query("SELECT entity_id FROM catalog_product_entity LIMIT 1")->fetchColumn();
    if ($pid) {
        $pdo->prepare("INSERT INTO sales_cart_product (cart_id, product_id, quantity) VALUES (?, ?, 1)")->execute([$cartId, $pid]);
        $productId = $pid;
    } else {
        die(json_encode(['status' => 'error', 'message' => 'No products']));
    }
}

// Prepare Request
$_POST['action'] = 'update';
$_POST['product_id'] = $productId;
$_POST['qty_action'] = 'plus';

// Capture Output
ob_start();
try {
    $controller = new \App\Controller\Cart();
    $controller->handlerAction();
} catch (Throwable $e) {
    echo "<br /><b>Fatal error</b>: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine();
}
$output = ob_get_clean();

// Analyze
$json = json_decode($output, true);
if ($json === null) {
    echo "INVALID JSON DETECTED. RAW OUTPUT:\n";
    echo $output;
} else {
    echo "VALID JSON:\n";
    print_r($json);
}
