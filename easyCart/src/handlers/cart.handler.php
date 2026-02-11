<?php
require_once __DIR__ . '/../init.php';

use App\Controller\Controller_Cart;

$controller = new Controller_Cart();
$controller->handlerAction();