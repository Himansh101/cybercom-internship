<?php
namespace App\Controllers;

use App\Models\Customer;

class SignupController extends BaseController
{
    public function index()
    {
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullName = $_POST['full_name'];
            $email = $_POST['email'];
            $mobile = $_POST['mobile'];
            $password = $_POST['password'];
            $confirm = $_POST['confirm_password'];

            // Basic validation
            if (strlen($fullName) < 3 || !preg_match("/^[a-zA-Z\s]+$/", $fullName)) {
                $error = "Invalid name. Must be at least 3 characters and contain only letters.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email address.";
            } elseif (!preg_match("/^(\+91)[6-9][0-9]{9}$/", $mobile)) {
                $error = "Invalid mobile number. Must be in +91XXXXXXXXXX format.";
            } elseif ($password !== $confirm) {
                $error = "Passwords do not match!";
            } elseif (strlen($password) < 8) {
                $error = "Password must be at least 8 characters long!";
            } elseif (!preg_match("/[a-z]/", $password)) {
                $error = "Password must contain at least one lowercase letter!";
            } elseif (!preg_match("/[A-Z]/", $password)) {
                $error = "Password must contain at least one uppercase letter!";
            } elseif (!preg_match("/[0-9]/", $password)) {
                $error = "Password must contain at least one number!";
            } else {
                $customerModel = new Customer();
                if ($customerModel->exists($email)) {
                    $error = "An account with this email already exists!";
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $customerModel->create([
                        'name' => $fullName,
                        'email' => $email,
                        'mobile' => $mobile,
                        'password' => $hashedPassword
                    ]);

                    header("Location: login?registered=true");
                    exit();
                }
            }
        }

        $pageTitle = 'EasyCart | Signup';
        $currentPage = 'signup';
        $extraStyles = ['auth.css'];
        $extraScripts = ['auth.js'];

        $this->render('signup', [
            'pageTitle' => $pageTitle,
            'currentPage' => $currentPage,
            'extraStyles' => $extraStyles,
            'extraScripts' => $extraScripts,
            'error' => $error
        ]);
    }
}
