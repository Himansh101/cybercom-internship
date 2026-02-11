<?php
require_once 'src/init.php';

use App\Controller\Controller_Home;

$controller = new Controller_Home();
$controller->indexAction();