<?php
// class Product {
//  public $name;
//  public $price;
//  public function __construct($name, $price) {
//  $this->name = $name;
//  $this->price = $price;
//  }
// }




// • Create a class Employee.

class Employee
{
    // • Define properties: public $name, private $salary.

    public $name;
    private $salary;

    //  Create a __construct() method to initialize name and salary when the object is created.
    public function __construct($name, $salary)
    {
        $this->name = $name;
        $this->salary = $salary;
    }
}


// Basic class and constructor

class User
{
    public string $name;

    // Constructor
    public function __construct(string $name)
    {
        $this->name = $name;
    }
}

$user1 = new User("Intern");
echo "User Name: " . $user1->name . "<br>";


// Multiple properties in constructor

class Product
{
    public string $name;
    public float $price;
    public int $quantity;

    public function __construct(string $name, float $price, int $quantity)
    {
        $this->name     = $name;
        $this->price    = $price;
        $this->quantity = $quantity;
    }
}

$product1 = new Product("Keyboard", 4500, 2);
echo "Product: {$product1->name}, Total: " . ($product1->price * $product1->quantity) . "<br>";


// Default values in constructor
class Category
{
    public string $name;
    public bool $active;

    public function __construct(string $name, bool $active = true)
    {
        $this->name   = $name;
        $this->active = $active;
    }
}

$cat1 = new Category("Electronics");
$cat2 = new Category("Fashion", false);

var_dump($cat1);
var_dump($cat2);


// Private properties and getters

class Order
{
    private string $orderId;
    private float $amount;

    public function __construct(string $orderId, float $amount)
    {
        $this->orderId = $orderId;
        $this->amount  = $amount;
    }

    public function getOrderDetails(): string
    {
        return "Order ID: {$this->orderId}, Amount: ₹{$this->amount}";
    }
}

$order = new Order("ORD-1001", 8999);
echo $order->getOrderDetails() . "<br>";


// Constructor with validation logic

class Customer
{
    public string $email;

    public function __construct(string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email address");
        }

        $this->email = $email;
    }
}

try {
    $customer = new Customer("test@example.com");
    echo "Customer Email: {$customer->email}" . "<br>";
} catch (Exception $e) {
    echo $e->getMessage() . "<br>";
}


// Real world example

class CartItem
{
    public string $productName;
    public float $price;
    public int $quantity;

    public function __construct(string $productName, float $price, int $quantity)
    {
        $this->productName = $productName;
        $this->price       = $price;
        $this->quantity    = $quantity;
    }

    public function getTotal(): float
    {
        return $this->price * $this->quantity;
    }
}

$item = new CartItem("Mechanical Keyboard", 4500, 2);
echo "Cart Total: ₹" . $item->getTotal() . "<br>";


// Constructor Promotion 

class Shipping
{
    public function __construct(
        public string $method,
        public float $cost
    ) {}
}

$shipping = new Shipping("Express", 150);
echo "Shipping: {$shipping->method}, Cost: ₹{$shipping->cost}" . "<br>";

?>