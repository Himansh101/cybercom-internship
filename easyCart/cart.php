<?php
require_once __DIR__ . '/src/init.php';

use App\Controllers\CartController;

$controller = new CartController();
$controller->index();
?>