<?php
require_once __DIR__ . '/src/init.php';

use App\Controllers\IndexController;

$controller = new IndexController();
$controller->index();
?>