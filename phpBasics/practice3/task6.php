<?php
// declare(strict_types=1);

$email = " User@Example.com ";
$cleanEmail = strtolower(trim($email)); // "user@example.com"
$files = ["image.jpg", "script.js", "style.css"];
$parts = explode(".", $files[0]); // Returns ["image", "jpg"]

// String: Create a string ' Hello World '. Trim it, convert to lowercase, and replace 'World' with 'PHP'

$string = " Hello World ";
$string = trim($string);
$string = strtolower($string);
$string = str_replace("world", "php", $string);
echo $string . "<br>";

// Array: Create an array of numbers. Check if '5' exists (in_array), add a number (array_push), and merge it with another array (array_merge).

$numbers = [1, 2, 3, 4, 6];

if (in_array(5, $numbers)) {
    echo "5 exists in the array.<br>";
} else {
    echo "5 does not exist in the array.<br>";
}

array_push($numbers, 5);
echo "Array after adding 5: " . implode(", ", $numbers) . "<br>";

$anotherArray = [7, 8, 9];
$mergedArray = array_merge($numbers, $anotherArray);
echo "Merged array: " . implode(", ", $mergedArray) . "<br>";

// String functions

// strlen()
$text = "Hello PHP";
echo "Length: " . strlen($text) . "<br>";

// strtoupper() & strtolower()
echo strtoupper($text) . "<br>";
echo strtolower($text) . "<br>";

// ucfirst() & ucwords()
echo ucfirst("php developer") . "<br>";
echo ucwords("php web developer") . "<br>";

// substr()
echo substr($text, 0, 5) . "<br>";

// str_replace()
echo str_replace("PHP", "World", $text) . "<br>";


// Number and math funtions

// abs()
echo abs(-50) . "<br>";

// round(), ceil(), floor()
echo round(12.6) . "<br>";
echo ceil(12.1) . "<br>";
echo floor(12.9) . "<br>";

// rand()
echo rand(1, 100) . "<br>";

// number_format()
$price = 12345.678;
echo number_format($price, 2) . "<br>";


// Array functions

$products = ["Laptop", "Mobile", "Tablet"];

// count()
echo count($products) . "<br>";

// in_array()
var_dump(in_array("Mobile", $products));
echo "<br>";

// array_push()
array_push($products, "Camera");
echo "<br>";

// array_pop()
array_pop($products);

// array_merge()
$moreProducts = ["Keyboard", "Mouse"];
$allProducts = array_merge($products, $moreProducts);

print_r($allProducts);

// array_keys() & array_values()
$productPrices = [
    "Laptop" => 50000,
    "Mobile" => 20000
];

print_r(array_keys($productPrices));
print_r(array_values($productPrices));


// Date and time functions

// date()
echo date("Y-m-d") . "<br>";
echo date("d-m-Y H:i:s") . "<br>";

// time()
$currentTime = time();
echo $currentTime . "<br>";

// strtotime()
echo date("Y-m-d", strtotime("+5 days")) . "<br>";


// File and path functions

// basename()
$filePath = "/var/www/html/index.php";
echo basename($filePath) . "<br>";

// dirname()
echo dirname($filePath) . "<br>";

// file_exists()
var_dump(file_exists("built_in_functions.php"));


// Variable checking functions

$username = "Intern";

// isset()
var_dump(isset($username));

// empty()
var_dump(empty($username));

// unset()
unset($username);
var_dump(isset($username));


// Filter and validation functions

// filter_var() - email
$email = "test@example.com";
var_dump(filter_var($email, FILTER_VALIDATE_EMAIL));

// sanitize string
$userInput = "<script>alert('XSS')</script>";
echo filter_var($userInput, FILTER_SANITIZE_STRING) . "<br>";


// very useful debugging functions

$data = [
    "name" => "Intern",
    "role" => "Developer",
    "active" => true
];

// print_r()
print_r($data);

// var_dump()
var_dump($data);

// die() / exit()
if (!isset($data["name"])) {
    die("Name not found!");
}


// real world example

$product = [
    "name" => "Mechanical Keyboard",
    "price" => 4500,
    "quantity" => 3
];

$total = $product["price"] * $product["quantity"];

echo "Product: " . strtoupper($product["name"]) . "<br>";
echo "Total: ₹" . number_format($total, 2) . "<br>";

if ($total > 10000) {
    echo "Free Shipping Applied" . "<br>";
} else {
    echo "Shipping Charges Apply" . "<br>";
}


?>
