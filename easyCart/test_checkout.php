<?php
// Mock Session for CLI
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/src/init.php';

// Get a valid cart
$stmt = $pdo->query("SELECT cart_id FROM sales_cart WHERE is_active = true LIMIT 1");
$cartId = $stmt->fetchColumn();

if (!$cartId) {
    die("No active cart found for testing.\n");
}

$_SESSION['cart_id'] = $cartId;
$_SESSION['user_id'] = 1; // Assume user 1 exists or null

use App\Controller\Checkout;

echo "Testing Checkout Controller with Cart ID: $cartId\n";
echo "Class exists? " . (class_exists(Checkout::class) ? "Yes" : "No") . "\n";

$c = new Checkout();

ob_start();
try {
    $c->indexAction();
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage();
}
$output = ob_get_clean();

echo "Output captured (" . strlen($output) . " bytes).\n";
file_put_contents('debug_checkout_output.html', $output);

// Check if GST is in output (since I var_dumped keys)
// My var_dump in src/View/Checkout.php printed array keys
// Look for [gst] in the output (HTML encoded?)
if (strpos($output, '[gst]') !== false) {
    echo "SUCCESS: 'gst' key found in View data.\n";
} else {
    echo "FAILURE: 'gst' key NOT found in View debug output.\n";
}
