<?php
// declare(strict_types=1);
// function getFullName(string $first, string $last): string {
//  return $first . " " . $last;
// }
// echo getFullName("John", "Doe");
// echo getFullName("John", 123); // This would trigger a TypeError

// • Enable strict types: declare(strict_types=1);
declare(strict_types=1);

// • Create a function calculateTotal(float $price, int $qty): float.
function calculateTotal(float $price, int $qty): float{
    return $price * $qty;
}

echo calculateTotal(10, 5) . "<br>";

//  Demonstrate 'Global' vs 'Local' variable scope inside a function.

$variable = "Global Scope";

function testScope() {
    $variable = "Local Scope";
    echo "Inside function (Local): " . $variable . "<br>";
    
    global $variable;
    echo "Inside function (Global): " . $variable . "<br>";
}

testScope();
echo "Outside function (Global): " . $variable . "<br>";

//Basic Custom Function

// Task 1: Simple function
function greetUser(): string {
    return "Hello Intern!";
}

echo greetUser() . "<br>";

// Task 2: Function with parameter
function greet(string $name): string {
    return "Hello " . $name;
}

echo greet("Rahul") . "<br>";


//Strict types and return types

// Task 3: Integer type hints
function addNumbers(int $a, int $b): int {
    return $a + $b;
}

echo addNumbers(10, 20) . "<br>";

// Task 4: Float return type
function calculateGST(float $amount): float {
    return $amount * 0.18;
}

echo calculateGST(1000) . "<br>";


//Default and nullable parameters

// Task 5: Default parameter
function applyDiscount(float $price, float $discount = 10): float {
    return $price - ($price * $discount / 100);
}

echo applyDiscount(2000) . "<br>";

// Task 6: Nullable parameter
function welcomeUser(?string $name): string {
    return $name === null ? "Welcome Guest" : "Welcome $name";
}

echo welcomeUser(null) . "<br>";
echo welcomeUser("Intern") . "<br>";


//Variable scopes

// Task 7: Local scope
function showMessage(): void {
    $message = "Inside Function";
    echo $message . "<br>";
}

showMessage();

// Task 8: Global scope
$totalAmount = 500;

function showTotal(): void {
    global $totalAmount;
    echo "Total Amount: $totalAmount" . "<br>";
}

showTotal();

// Task 9: Static variable
function visitCounter(): void {
    static $count = 0;
    $count++;
    echo "Visit Count: $count" . "<br>";
}

visitCounter();
visitCounter();
visitCounter();


//Funcitions returning arrays

// Task 10: Return array
function getProduct(): array {
    return [
        'name' => 'Mechanical Keyboard',
        'price' => 4500,
        'in_stock' => true
    ];
}

print_r(getProduct());


//Function using arrays

// Task 11: Shipping type logic
function getShippingType(float $price): string {
    return $price < 300 ? 'express' : 'freight';
}

echo getShippingType(250) . "<br>";
echo getShippingType(500) . "<br>";

// Task 12: Stock validation
function isInStock(int $quantity): bool {
    return $quantity > 0;
}

var_dump(isInStock(10));
var_dump(isInStock(0));


//Function Scope

// Task 13: Nested function
function outerFunction(): void {
    function innerFunction(): string {
        return "Inner Function Executed";
    }
}

outerFunction();
echo innerFunction() . "<br>";


//Real World use case

function calculateOrderTotal(
    float $price,
    int $quantity,
    ?float $discount = null
): float {
    $subtotal = $price * $quantity;

    if ($discount !== null) {
        $subtotal -= ($subtotal * $discount / 100);
    }

    return $subtotal;
}

echo calculateOrderTotal(500, 3, 10) . "<br>";
echo calculateOrderTotal(1000, 1) . "<br>";

?>

