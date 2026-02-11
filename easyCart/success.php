<?php
require_once 'src/init.php';

use App\Controller\Order;

// Optional: Ensure user is logged in
// Legacy might have shown a generic success page?
// Usually success page is just a template.
// I'll make a successAction in Order Controller.

$controller = new Order();
// $controller->successAction(); 
// Wait, I haven't implemented successAction yet.
// I'll redirect to orders or implement it now.
// Legacy 'success_page' usually just shows message from session.
// `orders.view.php` has a success banner block `if (isset($_SESSION['order_success']))`.
// So maybe `success.php` isn't needed if we redirect to `orders.php`?
// `checkout.handler.php` returns JSON `message`. Front end logic likely redirects.
// If valid front-end redirects to `success.php`, I need this file.
// If it redirects to `orders.php`, I don't.
// Let's implement successAction to render `orders.view.php` with a flag?
// Or just redirect to `orders`.
header("Location: orders");
exit;
