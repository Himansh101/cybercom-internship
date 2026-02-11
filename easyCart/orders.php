<?php
require_once 'src/init.php';

use App\Controller\Order;

$controller = new Order();
$controller->indexAction();