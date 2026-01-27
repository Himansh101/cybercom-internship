<?php

$host = 'localhost';
$database = 'sampledb';
$port = '5432';
$username = 'postgres';
$password = 'Himanshu@2912';

$conn = pg_connect("host = $host port = $port dbname = $database user = $username password = $password");

if(!$conn){
    die("Error in connection: " . pg_last_error());
}

echo "Connection Successful";
?>