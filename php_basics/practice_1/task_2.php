<?php
// $student = [
//  "name" => "Rahul",
//  "age" => 23,
//  "skills" => ["PHP", "HTML", "CSS"]
// ];
// echo "<pre>"; // Formatting for readability
// print_r($student);
// echo "</pre>";

$person = [
    "name"=>"John", 
    "age"=>20, 
    "skills"=>["HTML", "CSS", "JS", "PHP"]
];

echo "<pre>"; 
print_r($person);
echo "</pre>";

echo "<pre>"; 
var_dump($person);
echo "</pre>";

// Topic: Arrays & Debugging

// Indexed Array
$colors = ["Red", "Green", "Blue"];
echo "Indexed Array:<br>";
print_r($colors);

// Access element
echo "First color: " . $colors[0] . "<br><br>";

// Associative Array
$user = [
    "name" => "Intern",
    "email" => "intern@example.com",
    "age" => 22
];
echo "Associative Array:<br>";
print_r($user);

// Access associative value
echo "User Email: " . $user['email'] . "<br><br>";

// Array operations

// Add elements
$colors[] = "Yellow";
array_push($colors, "Black");

echo "After adding elements:<br>";
print_r($colors);

// Remove element
unset($colors[1]); // removes Green
echo "<br>After removing index 1:<br>";
print_r($colors);

// Count array
echo "<br>Total colors: " . count($colors) . "<br><br>";

// Multidimensional array

$products = [
    [
        "id" => 1,
        "name" => "Laptop",
        "price" => 50000
    ],
    [
        "id" => 2,
        "name" => "Mouse",
        "price" => 500
    ]
];

// Access nested value
echo "Second product name: " . $products[1]['name'] . "<br>";

// Loop through multidimensional array
foreach ($products as $product) {
    echo $product['name'] . " - ₹" . $product['price'] . "<br>";
}

echo "<br>";

// Array functions  

$numbers = [10, 20, 30, 40];

// array_sum
echo "Sum: " . array_sum($numbers) . "<br>";

// array_merge
$moreNumbers = [50, 60];
$merged = array_merge($numbers, $moreNumbers);
echo "Merged Array:<br>";
print_r($merged);

// in_array
echo "Is 20 present? ";
echo in_array(20, $numbers) ? "Yes<br><br>" : "No<br><br>";


// Debugging errors

// var_dump
echo "var_dump output:<br>";
var_dump($user);

// print_r with formatting
echo "<br>print_r output:<br>";
print_r($products);

// Debug and stop execution
echo "<br>Debug and Die:<br>";
var_dump($numbers);
// die("Script stopped here for debugging.<br>"); //this line will stop the script execution

//Common errors & safe checks

// Undefined index safe check
if (isset($user['address'])) {
    echo $user['address'];
} else {
    echo "Address not set<br>";
}

// Empty check
$cart = [];
if (empty($cart)) {
    echo "Cart is empty<br>";
}

// Null coalescing operator
$phone = $user['phone'] ?? 'Not Available';
echo "Phone: $phone<br>";


//Real world scenario

// Cart example
$cart = [
    ["product" => "Laptop", "qty" => 1, "price" => 50000],
    ["product" => "Mouse", "qty" => 2, "price" => 500]
];

$total = 0;
foreach ($cart as $item) {
    $itemTotal = $item['qty'] * $item['price'];
    $total += $itemTotal;
    echo $item['product'] . " Total: ₹" . $itemTotal . "<br>";
}

echo "Cart Grand Total: ₹" . $total . "<br>";

?>