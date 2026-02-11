<?php
require_once __DIR__ . '/../init.php';

use App\Controller\Checkout;
use App\Utils\Cart;
use App\Utils\Shipping;
use App\Utils\Coupon;
use App\Utils\Stripe;

$controller = new Checkout();
$controller->processAction();