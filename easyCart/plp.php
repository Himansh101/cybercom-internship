<?php
require_once __DIR__ . '/src/init.php';

use App\Controllers\PlpController;

$controller = new PlpController();
$controller->index();
?>