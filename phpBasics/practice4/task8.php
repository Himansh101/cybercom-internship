<?php

// declare(strict_types=1);

class Employee
{
    
    public $name;
    private $salary;
    
    public function __construct($name, $salary)
    {
        $this->name = $name;
        $this->salary = $salary;
    }

    //  In Employee, add a public method getSalary() to return the private salary.

    public function getSalary()
    {
        return $this->salary;
    }

    //  Add a public method setSalary($amount) to update the salary, but only if the $amount is positive.

    public function setSalary($amount)
        {
            if ($amount > 0) {
                $this->salary = $amount;
            } else {
                echo "Invalid salary amount.";
            }
        }
}

// Basic encapsulation

class User
{
    private string $name;

    public function setName(string $name): void
    {
        $this->name = ucfirst($name);
    }

    public function getName(): string
    {
        return $this->name;
    }
}

$user = new User();
$user->setName("intern");
echo "User Name: " . $user->getName() . "<br>";


// Validation using setter

class Account
{
    private float $balance = 0;

    public function setBalance(float $amount): void
    {
        if ($amount < 0) {
            throw new InvalidArgumentException("Balance cannot be negative");
        }
        $this->balance = $amount;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }
}

$account = new Account();
$account->setBalance(1500);
echo "Balance: ₹" . $account->getBalance() . "<br>";


//Read only properties

class Order
{
    private string $orderId;

    public function __construct(string $orderId)
    {
        $this->orderId = $orderId;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }
}

$order = new Order("ORD-5001");
echo "Order ID: " . $order->getOrderId() . "<br>";


// Business logic inside getter

class Product
{
    private string $name;
    private float $price;

    public function setName(string $name): void
    {
        $this->name = trim($name);
    }

    public function setPrice(float $price): void
    {
        if ($price <= 0) {
            throw new InvalidArgumentException("Price must be greater than zero");
        }
        $this->price = $price;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }
}

$product = new Product();
$product->setName(" Mechanical Keyboard ");
$product->setPrice(4500);
echo $product->getName() . " - ₹" . $product->getPrice() . "<br>";


// Calculted getter , no private property access

class CartItem
{
    private float $price;
    private int $quantity;

    public function __construct(float $price, int $quantity)
    {
        $this->price    = $price;
        $this->quantity = $quantity;
    }

    public function getTotal(): float
    {
        return $this->price * $this->quantity;
    }
}

$item = new CartItem(4500, 2);
echo "Cart Total: ₹" . $item->getTotal() . "<br>";

// Real world example

class Customer
{
    private string $email;

    public function setEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email");
        }
        $this->email = strtolower($email);
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}

$customer = new Customer();
$customer->setEmail("USER@EXAMPLE.COM");
echo "Customer Email: " . $customer->getEmail() . "<br>";


// Purpose of encapsulations

// This is NOT allowed (private property)
// echo $customer->email;

// Correct way
echo "Access via Getter works!" . "<br>";

?>
>