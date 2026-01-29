<?php
// $productName = "Laptop"; // String
// $price = 450.50; // Float
// $quantity = 5; // Integer
// $inStock = true; // Boolean
// // Concatenation example
// echo "The product " . $productName . " costs $" . $price;
// $arr = [1,2,3,5,6,7];
// $n = null;


/* PRACTICE: BASICS & VARIABLES (ALL LEVELS) */

$greeting = "Hello the year is";
$year = 2024;

echo $greeting . $year;

$isActive = false;
echo $isActive;



/* Focus: Variables, Data Types, Output */

// String
$name = "InternName";
echo "Name (String): $name<br>";

// Integer
$age = 21;
echo "Age (Integer): $age<br>";

// Float
$marks = 88.75;
echo "Marks (Float): $marks<br>";

// Boolean
$isLoggedIn = true;
echo "Is Logged In (Boolean): " . ($isLoggedIn ? "true" : "false") . "<br>";

// Null
$address = null;
echo "Address (Null): ";
var_dump($address);


/* Focus: Arrays & Constants */

// Indexed Array
$languages = ["PHP", "JavaScript", "Python"];
echo "Languages (Indexed Array):<br>";
print_r($languages);

// Associative Array
$user = [
    "username" => "intern01",
    "email" => "intern@example.com",
    "role" => "Intern"
];
echo "User Role: " . $user["role"] . "<br>";

// Constant
define("APP_NAME", "LearningPHP");
echo "App Name (Constant): " . APP_NAME . "<br>";



/* Focus: Variable Operations & Type Checking */

// Variable reassignment
$counter = 10;
$counter += 5;
echo "Counter after increment: $counter<br>";

// Type checking
echo "Is \$age an integer? ";
var_dump(is_int($age));

echo "Is \$marks a float? ";
var_dump(is_float($marks));

echo "Is \$languages an array? ";
var_dump(is_array($languages));



/* Focus: Variable Scope (Global & Local) */

$total = 100;

function addBonus() {
    global $total;
    $total += 50;
}

addBonus();
echo "Total after bonus (Global Scope): $total<br>";


/* Focus: Dynamic Variables & References */

// Variable variables
$varName = "course";
$$varName = "PHP Backend Development";

echo "Course (Variable Variable): $course<br>";

// Variable by reference
$a = 10;
$b = &$a;
$b = 20;

echo "Value of a after reference update: $a<br>";



/* Focus: Data Handling & Debugging */

// Mixed data structure
$data = [
    "id" => 1,
    "name" => "Intern",
    "skills" => ["PHP", "MySQL", "AJAX"],
    "active" => true
];

echo "Complete Data Structure:<br>";
print_r($data);

// Debug output
echo "Detailed dump:<br>";
var_dump($data);

?>
