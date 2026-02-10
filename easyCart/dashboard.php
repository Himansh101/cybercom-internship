<?php
require_once __DIR__ . '/src/init.php';

use App\Controllers\DashboardController;

$controller = new DashboardController();
$controller->index();
?>