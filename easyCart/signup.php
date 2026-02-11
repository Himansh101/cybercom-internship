<?php
require_once 'src/init.php';

use App\Controller\Customer;

$controller = new Customer();
$controller->signupAction();