<?php
require_once __DIR__ . '/../init.php';

// Authentication Check
if (!$isLoggedIn) {
    header("Location: login");
    exit();
}

$userId = $_SESSION['user_id'];

// 3. Page Meta
$pageTitle = 'EasyCart | Dashboard';
$currentPage = 'dashboard';
$extraStyles = ['dashboard.css'];
$extraScripts = ['https://cdn.jsdelivr.net/npm/chart.js', 'dashboard.js'];

// Load View
require_once __DIR__ . '/../views/dashboard.view.php';
