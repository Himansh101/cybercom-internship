<?php
require_once __DIR__ . '/src/init.php';

use App\Controllers\SignupController;

$controller = new SignupController();
$controller->index();
?>