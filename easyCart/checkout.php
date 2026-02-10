<?php
require_once __DIR__ . '/src/init.php';

use App\Controllers\CheckoutController;

$controller = new CheckoutController();
$controller->index();
?>