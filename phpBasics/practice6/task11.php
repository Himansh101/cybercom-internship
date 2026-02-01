<?php
// // Setting cookie (must be before any HTML output)
// setcookie("theme", "dark", time() + 3600, "/");

// // Retrieving
// if(isset($_COOKIE["theme"])) {
//     echo "Theme is: " . $_COOKIE["theme"];
// }

// Set a cookie named 'user_preference' with the value 'dark_mode' that expires in 1 hour.
setcookie("user_preference", "dark_mode", time() + 3600, "/");

// Write a script to check isset($_COOKIE['user_preference']) and print the value.
if(isset($_COOKIE["user_preference"])){
    echo "User preference is: " . $_COOKIE["user_preference"];
}

// IMPORTANT: Cookies must be set BEFORE any HTML output

// Set cookies

// Basic cookie
setcookie("username", "Intern", time() + 3600, "/");

// Cookie with array value (cart example)
setcookie("cart[item_id]", "101", time() + 3600, "/");
setcookie("cart[qty]", "2", time() + 3600, "/");

// Secure cookie (used in login systems)
setcookie(
    "auth_token",
    "secure_token_123",
    time() + 3600,
    "/",
    "",
    false, // set true if HTTPS
    true   // HttpOnly
);

// Shipping method (e-commerce use case)
setcookie("shipping_method", "express", time() + 86400, "/");

?>

<!DOCTYPE html>
<html>
<head>
    <title>PHP Cookies Demo</title>
</head>
<body>

<h2>🍪 PHP Cookies Demo</h2>

<?php

// Reading cookies

echo "<h3>Reading Cookies</h3>";

if (isset($_COOKIE['username'])) {
    echo "Username: " . $_COOKIE['username'] . "<br>";
} else {
    echo "Username cookie not set<br>";
}

if (isset($_COOKIE['cart'])) {
    echo "Cart Item ID: " . $_COOKIE['cart']['item_id'] . "<br>";
    echo "Cart Quantity: " . $_COOKIE['cart']['qty'] . "<br>";
}

if (isset($_COOKIE['shipping_method'])) {
    echo "Selected Shipping Method: " . $_COOKIE['shipping_method'] . "<br>";
}

// Update cookie

setcookie("username", "SeniorIntern", time() + 3600, "/");
echo "<br>Username cookie updated.<br>";

// Delete cookie

if (isset($_GET['logout'])) {
    setcookie("username", "", time() - 3600, "/");
    setcookie("auth_token", "", time() - 3600, "/");
    echo "<br>Cookies deleted successfully.<br>";
}

// Debugging all cookies

echo "<h3>All Cookies</h3>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";
?>

<a href="?logout=true">Logout (Delete Cookies)</a>

</body>
</html>