<?php
// Foreach example with associative array
$prices = ["Apple" => 120, "Banana" => 60, "Mango" => 150];
foreach ($prices as $fruit => $price) {
 echo "The price of $fruit is Rs. $price <br>";
}

// Use for loop to print a multiplication table for 5.
for($i = 1; $i <= 10; $i++){
    echo "5 * $i = " . 5 * $i . "<br>";
}

// Use foreach to iterate through the $student array from Practice 1 and print 'Key: Value'.
$student = [
    "name" => "John",
    "age" => 20,
    "skills" => ["HTML", "CSS", "JS", "PHP"]
];
foreach ($student as $key => $value) {
    echo "$key: $value <br>";
}

// Topic: Iteration (for, while, foreach)

// Basic for loop

// Print numbers 1 to 5
for ($i = 1; $i <= 5; $i++) {
    echo "Number: $i<br>";
}

echo "<br>";


//for loop with calculation

// Print square of numbers 1 to 5
for ($i = 1; $i <= 5; $i++) {
    echo "Square of $i = " . ($i * $i) . "<br>";
}

echo "<br>";


//while loop

// Countdown from 5 to 1
$count = 5;
while ($count > 0) {
    echo "Countdown: $count<br>";
    $count--;
}

echo "<br>";

//do-while loop

// Runs at least once
$x = 2;
do {
    echo "Value: $x<br>";
    $x++;
} while ($x <= 3);

echo "<br>";


//foreach loop with indexed array

$colors = ["Red", "Green", "Blue"];

foreach ($colors as $color) {
    echo "Color: $color<br>";
}

echo "<br>";


//foreach loop with associative array

$user = [
    "name" => "Intern",
    "role" => "Developer",
    "age"  => 21
];

foreach ($user as $key => $value) {
    echo ucfirst($key) . ": $value<br>";
}

echo "<br>";

//break and continue

// Stop loop when number is 4
for ($i = 1; $i <= 6; $i++) {
    if ($i == 4) {
        break;
    }
    echo "Value: $i<br>";
}

echo "<br>";

// Skip number 3
for ($i = 1; $i <= 5; $i++) {
    if ($i == 3) {
        continue;
    }
    echo "Value: $i<br>";
}

echo "<br>";

//star patten

/*
*
**
***
****
*****
*/

for ($i = 1; $i <= 5; $i++) {
    for ($j = 1; $j <= $i; $j++) {
        echo "*";
    }
    echo "<br>";
}

echo "<br>";

//inverted star pattern

/*
*****
****
***
**
*
*/

for ($i = 5; $i >= 1; $i--) {
    for ($j = 1; $j <= $i; $j++) {
        echo "*";
    }
    echo "<br>";
}

echo "<br>";

//number pattern

/*
1
12
123
1234
12345
*/

for ($i = 1; $i <= 5; $i++) {
    for ($j = 1; $j <= $i; $j++) {
        echo $j;
    }
    echo "<br>";
}

echo "<br>";



//pyramid star pattern
/*
    *
   ***
  *****
 *******
*********
*/

$rows = 5;
for ($i = 1; $i <= $rows; $i++) {
    for ($space = $rows - $i; $space > 0; $space--) {
        echo " ";
    }
    for ($star = 1; $star <= (2 * $i - 1); $star++) {
        echo "*";
    }
    echo "<br>";
}

echo "<br>";

//real world scenario

$cart = [
    ["name" => "Laptop", "price" => 50000, "qty" => 1],
    ["name" => "Mouse",  "price" => 500,   "qty" => 2],
    ["name" => "Bag",    "price" => 1200,  "qty" => 1]
];

$subtotal = 0;

foreach ($cart as $item) {
    $itemTotal = $item["price"] * $item["qty"];
    $subtotal += $itemTotal;

    echo $item["name"] . " - ₹" . $itemTotal . "<br>";
}

echo "Subtotal: ₹" . $subtotal . "<br>";


?>