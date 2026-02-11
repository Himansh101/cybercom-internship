<?php
require_once 'src/init.php';

use App\Controller\Controller_Checkout;

$controller = new Controller_Checkout();
$controller->indexAction();