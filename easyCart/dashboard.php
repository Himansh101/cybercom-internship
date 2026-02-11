<?php
require_once 'src/init.php';

use App\Controller\Controller_Dashboard;

$controller = new Controller_Dashboard();
$controller->indexAction();