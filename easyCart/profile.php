<?php
require_once 'src/init.php';

use App\Controller\Profile;

$controller = new Profile();
$controller->indexAction();