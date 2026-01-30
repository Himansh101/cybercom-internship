<?php

class Employee
{

    public $name;
    private $salary;

    public function __construct($name, $salary)
    {
        $this->name = $name;
        $this->salary = $salary;
    }

    public function getSalary()
    {
        return $this->salary;
    }

    public function setSalary($amount)
    {
        if ($amount > 0) {
            $this->salary = $amount;
        } else {
            echo "Invalid salary amount.";
        }
    }

    public function getDetails()
    {
        echo "Name: " . $this->name . ", Salary: " . $this->salary;
    }
}

// •	Create a class Manager that extends Employee.
class Manager extends Employee
{
    // •	Add a property $department specific to Manager.
    public $department;
    
// •	Override a method (e.g., getDetails) to include the department name.
    public function getDetails()
    {
        return $this->name . " manages the " . $this->department . " department.";
    }
}

// Basic Inheritance

class User
{
    protected string $name;

    public function setName(string $name): void
    {
        $this->name = ucfirst($name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDashboard(): string
    {
        return "User Dashboard";
    }

     public function getDashboardTwo(): string
    {
        return "Base Dashboard";
    }
}

class Admin extends User
{
    public function getRole(): string
    {
        return "Admin";
    }
}

$admin = new Admin("InterOne", 12);
$admin->setName("intern");
echo $admin->getName() . " - " . $admin->getRole() . "<br>";

class UserOne
{
    protected string $name;

    public function __construct(string $name)
    {
        $this->name = ucfirst($name);
    }
}

class Customer extends UserOne
{
    private int $points;

    public function __construct(string $name, int $points)
    {
        parent::__construct($name);
        $this->points = $points;
    }

    public function getDetails(): string
    {
        return $this->name . " | Points: " . $this->points;
    }
}

$customer = new Customer("intern", 120);
echo $customer->getDetails();

//Method Overloading

class AdminOne extends User
{
    public function getDashboard(): string
    {
        return "Admin Dashboard";
    }
}

$user = new User();
$admin = new AdminOne();

echo $user->getDashboard() . "<br>";
echo $admin->getDashboard() . "<br>";

//Method Overriding

class AdminTwo extends User
{
    public function getDashboardTwo(): string
    {
        return parent::getDashboardTwo() . " + Admin Controls";
    }
}

$admin = new AdminTwo();
echo $admin->getDashboardTwo();

// Real world example

class Fruit {
  public $name;
  public $color;
  
  public function __construct($name, $color) {
    $this->name = $name;
    $this->color = $color; 
  }

  public function intro() {
    echo "The fruit is $this->name and the color is $this->color."; 
  }
}

class Strawberry extends Fruit {
  public $weight;
  
  public function __construct($name, $color, $weight) {
    $this->name = $name;
    $this->color = $color;
    $this->weight = $weight; 
  }
  
  public function intro() {
    echo "A $this->name is $this->color, and the weight is $this->weight gram."; 
  }
}

$strawberry = new Strawberry("Strawberry", "red", 50);
$strawberry->intro();

class Product
{
    protected string $name;
    protected float $price;

    public function __construct(string $name, float $price)
    {
        $this->name  = $name;
        $this->price = $price;
    }

    public function getPrice(): float
    {
        return $this->price;
    }
}

class DigitalProduct extends Product
{
    public function getPrice(): float
    {
        return $this->price; // no shipping
    }
}

class PhysicalProduct extends Product
{
    private float $shipping = 50;

    public function getPrice(): float
    {
        return $this->price + $this->shipping;
    }
}

$ebook = new DigitalProduct("PHP Ebook", 299);
$phone = new PhysicalProduct("Smart Phone", 12000);

echo "Ebook Price: ₹" . $ebook->getPrice() . "<br>";
echo "Phone Price: ₹" . $phone->getPrice();

?>
