<?php
require_once __DIR__ . '/../init.php';

// Check if user is logged in
if (!$isLoggedIn) {
    header("Location: login");
    exit();
}

// Page meta
$pageTitle = 'EasyCart | My Profile';
$currentPage = 'profile';
$extraStyles = ['profile.css'];
$extraScripts = ['profile.js'];

// Fetch full user data including address
$stmt = $pdo->prepare("SELECT entity_id, name, email, mobile, street_address, city, pincode, is_admin, created_at FROM customer_entity WHERE entity_id = ?");
$stmt->execute([$userId]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

// Load View
require_once __DIR__ . '/../views/profile.view.php';
?>