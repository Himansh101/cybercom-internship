<?php
require_once 'src/init.php';

use App\Controller\Controller_Customer;

$controller = new Controller_Customer();
$controller->loginAction();