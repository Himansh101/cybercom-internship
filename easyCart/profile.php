<?php
require_once 'src/init.php';

use App\Controller\Controller_Profile;

$controller = new Controller_Profile();
$controller->indexAction();