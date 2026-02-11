<?php
require_once 'src/init.php';

use App\Controller\Cart;

$controller = new Cart();
$controller->indexAction();