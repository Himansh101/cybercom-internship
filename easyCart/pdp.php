<?php
require_once __DIR__ . '/src/init.php';

use App\Controllers\PdpController;

$controller = new PdpController();
$controller->index();
?>