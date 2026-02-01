<?php
require 'FileA.php';
require 'FileB.php';

use Library\Database\Connection as DBConnection;
use Library\API\Connection as APIConnection;

$db = new DBConnection();
$api = new APIConnection();
?>
