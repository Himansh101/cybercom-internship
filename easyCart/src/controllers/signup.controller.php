<?php
require_once __DIR__ . '/../init.php';

// Handle form submission logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['full_name'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Basic validation
    if (strlen($fullName) < 3 || !preg_match("/^[a-zA-Z\s]+$/", $fullName)) {
        $error = "Invalid name. Must be at least 3 characters and contain only letters.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif (!preg_match("/^(\+91)[6-9][0-9]{9}$/", $mobile)) {
        $error = "Invalid mobile number. Must be in +91XXXXXXXXXX format.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match!";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long!";
    } elseif (!preg_match("/[a-z]/", $password)) {
        $error = "Password must contain at least one lowercase letter!";
    } elseif (!preg_match("/[A-Z]/", $password)) {
        $error = "Password must contain at least one uppercase letter!";
    } elseif (!preg_match("/[0-9]/", $password)) {
        $error = "Password must contain at least one number!";
    } else {
        // Check if user already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM customer_entity WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetchColumn() > 0) {
            $error = "An account with this email already exists!";
        } else {
            // Create new user in Database
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO customer_entity (name, email, mobile, password, created_at) VALUES (:name, :email, :mobile, :password, :created)");
            $stmt->execute([
                ':name' => $fullName,
                ':email' => $email,
                ':mobile' => $mobile,
                ':password' => $hashedPassword,
                ':created' => date('Y-m-d H:i:s')
            ]);

            // Redirect to login with success message
            header("Location: login?registered=true");
            exit();
        }
    }
}

// Page meta
$pageTitle = 'EasyCart | Signup';
$currentPage = 'signup';
$extraStyles = ['auth.css'];
$extraScripts = ['auth.js'];

// Load View
require_once __DIR__ . '/../views/signup.view.php';
?>