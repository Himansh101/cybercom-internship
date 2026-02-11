<?php
require_once 'src/init.php';

use App\Controller\Dashboard;

$controller = new Dashboard();
$controller->indexAction();