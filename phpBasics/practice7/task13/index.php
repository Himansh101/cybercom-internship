<?php
require 'Models/User.php';
require 'Admin/User.php';

use Models\User as CustomerUser;
use Admin\User as AdminUser;

$customer = new CustomerUser();
$admin = new AdminUser();

echo $customer->getRole() . "<br>";
echo $admin->getRole();
