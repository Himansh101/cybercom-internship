<?php
require_once __DIR__ . '/../init.php';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (empty($password)) {
        $error = "Password cannot be empty.";
    } else {
        // Query database for user
        $stmt = $pdo->prepare("SELECT * FROM customer_entity WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $dbUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dbUser && password_verify($password, $dbUser['password'])) {
            // Successful login - store ONLY user_id in session
            $_SESSION['user_id'] = $dbUser['entity_id'];

            // Sync cart from database
            $userCartId = mergeCartOnLogin($pdo, $dbUser['entity_id']);
            $_SESSION['cart_id'] = $userCartId;
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}

// Page meta
$pageTitle = 'EasyCart | Login';
$currentPage = 'login';
$extraStyles = ['auth.css'];
$extraScripts = ['auth.js'];

$signup_success = isset($_GET['registered']) && $_GET['registered'] === 'true';

// Load View
require_once __DIR__ . '/../views/login.view.php';
?>
