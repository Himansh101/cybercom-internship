<?php
require_once 'src/init.php';

use App\Controller\Home;

$controller = new Home();
$controller->indexAction();