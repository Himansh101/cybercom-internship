<?php
session_start();
// $_SESSION['cart_count'] = 5;
// echo "Items in cart: " . $_SESSION['cart_count'];

// Page 1: session_start(). Set $_SESSION['username'] = 'InternName'.

$_SESSION['username'] = 'InternName';

// Redirect to Page 2
header('Location: page2.php');
exit();


