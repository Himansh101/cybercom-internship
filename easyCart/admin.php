<?php
require_once 'src/init.php';

use App\Controller\Controller_Admin;

$controller = new Controller_Admin();
$controller->indexAction();