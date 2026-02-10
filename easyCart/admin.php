<?php
require_once __DIR__ . '/src/init.php';

use App\Controllers\AdminController;

$controller = new AdminController();
$controller->index();
?>