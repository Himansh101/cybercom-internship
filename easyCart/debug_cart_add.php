<?php
// Simulate environment
ini_set('display_errors', 1);
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error) {
        echo "\nFATAL ERROR:\n";
        print_r($error);
    }
});
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['action'] = 'add';
$_POST['product_id'] = 13; // Use the ID from previous debug (Optical Keyboard)

// Capture output
ob_start();
require_once __DIR__ . '/src/init.php';

// Mock Session if needed (init.php starts it)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // Mock user
}

// Instantiate Controller and call handler
$controller = new \App\Controller\Cart();
$controller->handlerAction();

$output = ob_get_clean();
echo "--- OUTPUT START ---\n";
echo $output;
echo "\n--- OUTPUT END ---\n";
