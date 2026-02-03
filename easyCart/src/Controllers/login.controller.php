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
        // Load users from JSON file
        $users = [];
        $file = __DIR__ . '/../../users.json';
        if (file_exists($file)) {
            $json_data = file_get_contents($file);
            $users = json_decode($json_data, true) ?? [];
        }

        $userFound = false;
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                if (password_verify($password, $user['password'])) {
                    // Successful login
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'mobile' => $user['mobile']
                    ];

                    // Restore cart
                    if (isset($user['cart']) && is_array($user['cart'])) {
                        $_SESSION['cart'] = $user['cart'];
                    }
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "Invalid email or password.";
                }
                $userFound = true;
                break;
            }
        }

        if (!$userFound) {
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
require_once __DIR__ . '/../Views/login.view.php';
?>
