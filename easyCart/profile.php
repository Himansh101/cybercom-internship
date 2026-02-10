<?php
require_once __DIR__ . '/src/init.php';

use App\Controllers\ProfileController;

$controller = new ProfileController();
$controller->index();
?>