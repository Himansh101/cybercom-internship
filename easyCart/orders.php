<?php
require_once __DIR__ . '/src/init.php';

use App\Controllers\OrderController;

$controller = new OrderController();
$controller->index();
?>