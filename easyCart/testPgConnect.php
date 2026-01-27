<?php

$host = '';
$database = '';
$port = '';
$username = '';
$password = '';

$conn = pg_connect("host = $host port = $port dbname = $database user = $username password = $password");

if(!$conn){
    die("Error in connection: " . pg_last_error());
}

echo "Connection Successful";
?>