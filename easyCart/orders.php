<?php
require_once 'src/init.php';

use App\Controller\Controller_Order;

$controller = new Controller_Order();
$controller->indexAction();