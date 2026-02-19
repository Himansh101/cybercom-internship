<?php
echo "<pre>";

class Engine
{
    protected $horsePower = 100;

    public function setHorsePower($hp)
    {
        $this->horsePower = $hp;
    }

    public function getHorsePower()
    {
        return $this->horsePower;
    }
}

class Car
{
    protected $engine = null;

    public function engine()
    {
        if ($this->engine === null) {
            $this->engine = new Engine;
        }

        return $this->engine;
    }
}

// Usage
$car = new Car;

// set value inside Engine
$car->engine()->setHorsePower(250);

// get value from Engine
echo $car->engine()->getHorsePower();


// class Car extends Engine
// {
//     // Car now inherits everything from Engine
// }

// // Usage
// $car = new Car;

// // Directly using inherited methods
// $car->setHorsePower(250);
// echo $car->getHorsePower();

