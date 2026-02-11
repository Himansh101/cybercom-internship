<?php
require_once 'src/init.php';

use App\Controller\Admin;

$controller = new Admin();
$controller->indexAction();