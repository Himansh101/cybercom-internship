<?php
require_once 'src/init.php';

use App\Controller\Checkout;

$controller = new Checkout();
$controller->indexAction();