<?php
/**
 * Root shim for Logout
 * Delegates to the internal controller for logic processing.
 */
require_once __DIR__ . '/src/init.php';

use App\Controllers\LogoutController;

$controller = new LogoutController();
$controller->index();
?>