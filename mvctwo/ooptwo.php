<?php

class Logger
{
    private static ?Logger $instance = null;

    private function __construct() {}

    public static function getInstance(): Logger
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function log(string $message)
    {
        echo "Log: " . $message;
    }
}

$log1 = Logger::getInstance();
$log2 = Logger::getInstance();

var_dump($log1 === $log2); // true

//Eager initialization

// class Config
// {
//     private static Config $instance;

//     private function __construct() {}

//     public static function init()
//     {
//         self::$instance = new self();
//     }

//     public static function getInstance(): Config
//     {
//         return self::$instance;
//     }
// }

// $one = Config::init();
// $two = Config::init();

// print_r($one === $two);

class Config
{
    private static Config $instance;

    private function __construct() {}

    public static function init()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
    }

    public static function getInstance(): Config
    {
        if (!isset(self::$instance)) {
            throw new Exception("Config not initialized");
        }

        return self::$instance;
    }
}

Config::init();
var_dump(Config::getInstance());


//Thread safe singleton

// class SafeSingleton
// {
//     private static ?SafeSingleton $instance = null;

//     private function __construct() {}

//     public static function getInstance(): SafeSingleton
//     {
//         if (self::$instance === null) {
//             // In real multithreaded env, lock here
//             self::$instance = new self();
//         }

//         return self::$instance;
//     }
// }


class SafeSingleton
{
    private static ?SafeSingleton $instance = null;

    private function __construct() {}

    private function __clone() {}

    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    public static function getInstance(): SafeSingleton
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}

$s1 = SafeSingleton::getInstance();
$s2 = SafeSingleton::getInstance();
var_dump($s1 === $s2);

$s3 = clone $s1;

$s4 = unserialize(serialize($s1));

//Singleton using trait (Reusable pattern)

trait SingletonTrait
{
    private static $instance;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}


class CacheManager
{
    use SingletonTrait;

    public function set($key, $value)
    {
        echo "Cached $key";
    }
}

$cache = CacheManager::getInstance();
$cache->set("user", "john");


//Multiton

class Database
{
    private static array $instances = [];
    private string $connectionName;

    private function __construct(string $connectionName)
    {
        $this->connectionName = $connectionName;
    }

    public static function getInstance(string $name): Database
    {
        if (!isset(self::$instances[$name])) {
            self::$instances[$name] = new self($name);
        }

        return self::$instances[$name];
    }
}

$readDb = Database::getInstance('read');
$writeDb = Database::getInstance('write');

var_dump($readDb === $writeDb);

//Container managed singleton

class Container
{
    private array $instances = [];

    public function singleton(string $key, callable $resolver)
    {
        if (!isset($this->instances[$key])) {
            $this->instances[$key] = $resolver($this);
        }
    }

    public function get(string $key)
    {
        return $this->instances[$key];
    }
}

$container = new Container();

$container->singleton('logger', function () {
    return new Logger();
});

$logger = $container->get('logger');

$logger->log("Hello");

//Bad Singleton

class BadSingleton
{
    public static $instance;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new BadSingleton();
        }

        return self::$instance;
    }
}

$b1 = BadSingleton::getInstance();
$b1 = false;
$b2 = BadSingleton::getInstance();
var_dump($b1 === $b2);
