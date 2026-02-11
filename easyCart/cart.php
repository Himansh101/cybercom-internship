<?php
require_once 'src/init.php';

use App\Controller\Controller_Cart;

$controller = new Controller_Cart();
$controller->indexAction();