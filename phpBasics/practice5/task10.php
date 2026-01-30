<?php
class User
{
    // Add __toString() to return a JSON representation of the object when 'echo $object' is called.
    public function __toString()
    {
        return "This is a User Object";
    }

    // Add __get($property) to handle access to undefined properties.
    public function __get($prop)
    {
        return "The property '$prop' does not exist.";
    }
}

$u = new User();
echo $u; // Output: This is a User Object
echo $u->address; // Output: The property 'address' does not exist.

// declare(strict_types=1);

class MagicDemo
{
    private string $name;
    private array $data = [];

    // 1. __construct()    Called when object is created
    public function __construct(string $name)
    {
        $this->name = $name;
        echo "__construct called<br>";
    }

    // 2. __destruct() Called when object is destroyed

    public function __destruct()
    {
        echo "__destruct called<br>";
    }

    // 3. __get()  Called when accessing inaccessible property
    public function __get(string $property)
    {
        echo "__get called for '$property'<br>";
        return $this->data[$property] ?? null;
    }

    // 4. __set() Called when setting inaccessible property
    public function __set(string $property, $value): void
    {
        echo "__set called for '$property'<br>";
        $this->data[$property] = $value;
    }

    // 5. __isset() Called when using isset() on inaccessible property

    public function __isset(string $property): bool
    {
        echo "__isset called for '$property'<br>";
        return isset($this->data[$property]);
    }

    // 6. __unset() Called when using unset() on inaccessible property

    public function __unset(string $property): void
    {
        echo "__unset called for '$property'<br>";
        unset($this->data[$property]);
    }

    // 7. __call() Called when calling inaccessible method

    public function __call(string $method, array $arguments)
    {
        echo "__call called for method '$method'<br>";
        echo "Arguments: " . implode(", ", $arguments) . "<br>";
    }

    // 8. __callStatic() Called when calling inaccessible static method

    public static function __callStatic(string $method, array $arguments)
    {
        echo "__callStatic called for static method '$method'<br>";
    }

    //9. __toString() Called when object is echoed
    
    public function __toString(): string
    {
        return "__toString called: Object name is {$this->name}<br>";
    }

    // 10. __invoke()  Called when object is used like a function
    
    public function __invoke($value)
    {
        echo "__invoke called with value: $value<br>";
    }

    // 11. __clone()  Called when object is cloned

    public function __clone()
    {
        echo "__clone called<br>";
    }

    // 12. __debugInfo()  Called by var_dump()

    public function __debugInfo(): array
    {
        return [
            'name' => $this->name,
            'custom_message' => 'Debug info modified by __debugInfo'
        ];
    }
}

// Testing all magic methods

$obj = new MagicDemo("Intern");

// __set & __get
$obj->email = "intern@example.com";
echo $obj->email . "<br>";

// __isset
var_dump(isset($obj->email));
echo "<br>";

// __unset
unset($obj->email);

// __call
$obj->undefinedMethod(10, 20);

// __callStatic
MagicDemo::staticTest();

// __toString
echo $obj;

// __invoke
$obj("Hello");

// __clone
$obj2 = clone $obj;

// __debugInfo
var_dump($obj);

//Example of serialize and unserialize

class UserOne
{
    public string $name;
    public string $email;
    private string $password;

    public function __construct(string $name, string $email, string $password)
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
    }

    public function __serialize(): array
    {
        return [
            'name'  => $this->name,
            'email' => $this->email
            // password excluded ❌ (security)
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->name  = $data['name'];
        $this->email = $data['email'];
        $this->password = '***'; // default / regenerated
    }
}

$user = new UserOne('Intern', 'intern@test.com', 'secret');

$serialized = serialize($user);
echo $serialized;