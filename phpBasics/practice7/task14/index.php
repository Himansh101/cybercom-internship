<?php
require 'App/Models/User.php';
require 'App/Services/Auth.php';
require 'App/Helpers/Formatter.php';

// Import classes
use App\Models\User;
use App\Services\Auth;

// Import function
use function App\Helpers\upper;

$user = new User("intern");
$auth = new Auth();

echo "Name: " . upper($user->getName()) . "<br>";
echo $auth->login();
