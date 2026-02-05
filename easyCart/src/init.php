<?php
// Central Initialization File
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/utils/shipping.utils.php';
require_once __DIR__ . '/utils/coupon.utils.php';
require_once __DIR__ . '/utils/cartsync.utils.php';
require_once __DIR__ . '/config/stripe.php';
require_once __DIR__ . '/utils/stripe.utils.php';

// Database initialization
$database = new Database();
$pdo = $database->getConnection();

if (!$pdo) {
    die("<div style='font-family: Arial; padding: 50px; text-align: center;'>
            <h1 style='color: #e11d48;'>Database Connection Failed</h1>
            <p>We're experiencing some technical difficulties. Please ensure your PostgreSQL server is running.</p>
            <p style='color: #64748b; font-size: 0.9rem;'>Error: Check your PHP error logs for more information.</p>
         </div>");
}

// Global variables
// 1. Session Migration/Clean-up (Ensuring only user_id and cart_id)
if (isset($_SESSION['user'])) {
    if (isset($_SESSION['user']['id']) && is_numeric($_SESSION['user']['id'])) {
        $_SESSION['user_id'] = $_SESSION['user']['id'];
    }
    unset($_SESSION['user']);
}

if (isset($_SESSION['user_id']) && !is_numeric($_SESSION['user_id'])) {
    unset($_SESSION['user_id']);
}

$isLoggedIn = isset($_SESSION['user_id']);
$userId = $_SESSION['user_id'] ?? null;
$user = null;

if ($isLoggedIn) {
    // Fetch full user record from DB (but keep session minimal)
    $stmtUser = $pdo->prepare("SELECT entity_id as id, name, email, mobile FROM customer_entity WHERE entity_id = ?");
    $stmtUser->execute([$userId]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    // Safety: If user deleted from DB but session exists
    if (!$user) {
        unset($_SESSION['user_id']);
        $isLoggedIn = false;
        $userId = null;
    }
}

// Ensure cart_id is tracked (Lazy: don't create if missing)
$cartId = getOrCreateCartId($pdo, $userId, false);

// Calculate total cart quantity (Distinct Items from DB)
$cartQuantity = 0;
if ($cartId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sales_cart_product WHERE cart_id = ?");
    $stmt->execute([$cartId]);
    $cartQuantity = (int)$stmt->fetchColumn();
}
?>
