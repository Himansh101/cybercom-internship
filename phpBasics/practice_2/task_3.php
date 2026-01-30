<?php
// $role = "admin";
// switch ($role) {
//  case "admin":
//  echo "Access to all levels.";
//  break;
//  case "editor":
//  echo "Access to post content.";
//  break;
//  default:
//  echo "Access denied.";
// }

// Create a variable $marks. Write an if-elseif chain to assign a grade (A, B, C, Fail)
$marks = 85;
if ($marks >= 90) {
    echo "Grade: A";
} elseif ($marks >= 80) {
    echo "Grade: B";
} elseif ($marks >= 70) {
    echo "Grade: C";
} else {
    echo "Grade: Fail";
}

// Create a variable $day. Use switch to print 'Weekend' for Sat/Sun and 'Weekday' for Mon-Fri.
$day = "Sat";
switch ($day) {
    case "Sat":
    case "Sun":
        echo "Weekend";
        break;
    case "Mon":
    case "Tue":
    case "Wed":
    case "Thu":
    case "Fri":
        echo "Weekday";
        break;
    default:
        echo "Invalid day";
}

// Topic: Decision Making (if, else, switch)

// Basic if else

// Simple if
$age = 20;
if ($age >= 18) {
    echo "User is eligible to vote<br>";
}

// if-else
$number = 7;
if ($number % 2 == 0) {
    echo "Number is Even<br>";
} else {
    echo "Number is Odd<br>";
}

echo "<br>";

// if elseif else

$marks = 82;

if ($marks >= 90) {
    echo "Grade: A+<br>";
} elseif ($marks >= 75) {
    echo "Grade: A<br>";
} elseif ($marks >= 60) {
    echo "Grade: B<br>";
} else {
    echo "Grade: C<br>";
}

echo "<br>";

// nested if else

$username = "intern";
$password = "12345";

if ($username === "intern") {
    if ($password === "12345") {
        echo "Login Successful<br>";
    } else {
        echo "Invalid Password<br>";
    }
} else {
    echo "Invalid Username<br>";
}

echo "<br>";

//switch statement

$day = 3;

switch ($day) {
    case 1:
        echo "Monday<br>";
        break;
    case 2:
        echo "Tuesday<br>";
        break;
    case 3:
        echo "Wednesday<br>";
        break;
    case 4:
        echo "Thursday<br>";
        break;
    case 5:
        echo "Friday<br>";
        break;
    default:
        echo "Weekend<br>";
}

echo "<br>";


//switch with string

$shippingMethod = "express";

switch ($shippingMethod) {
    case "standard":
        echo "Shipping Cost: ₹40<br>";
        break;
    case "express":
        echo "Shipping Cost: ₹80<br>";
        break;
    case "freight":
        echo "Shipping Cost: ₹200<br>";
        break;
    default:
        echo "Invalid Shipping Method<br>";
}

echo "<br>";

// ternary operator

$isLoggedIn = true;
echo $isLoggedIn ? "User is logged in<br>" : "User is guest<br>";


// Real world scenario

// Coupon logic
$coupon = "SAVE10";
$subtotal = 1000;
$discount = 0;

if ($coupon === "SAVE10") {
    $discount = $subtotal * 0.10;
} elseif ($coupon === "SAVE20") {
    $discount = $subtotal * 0.20;
} else {
    $discount = 0;
}

$finalTotal = $subtotal - $discount;
echo "Final Amount: ₹" . $finalTotal . "<br>";

// Order status using switch
$orderStatus = "shipped";

switch ($orderStatus) {
    case "pending":
        echo "Order is pending<br>";
        break;
    case "processing":
        echo "Order is being processed<br>";
        break;
    case "shipped":
        echo "Order has been shipped<br>";
        break;
    case "delivered":
        echo "Order delivered successfully<br>";
        break;
    default:
        echo "Unknown order status<br>";
    }
    
?>