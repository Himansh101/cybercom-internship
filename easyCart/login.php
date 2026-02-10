<?php
require_once __DIR__ . '/src/init.php';

use App\Controllers\LoginController;

$controller = new LoginController();
$controller->index();
?>