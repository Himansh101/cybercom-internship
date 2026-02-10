<?php
namespace App\Controllers;

use App\Models\Customer;

class LoginController extends BaseController
{
    public function index()
    {
        global $pdo;
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Please enter a valid email address.";
            } elseif (empty($password)) {
                $error = "Password cannot be empty.";
            } else {
                $customerModel = new Customer($pdo);
                $dbUser = $customerModel->findByEmail($email);

                if ($dbUser && password_verify($password, $dbUser['password'])) {
                    $_SESSION['user_id'] = $dbUser['entity_id'];

                    // Helper function from cartsync.utils.php
                    if (function_exists('mergeCartOnLogin')) {
                        $_SESSION['cart_id'] = mergeCartOnLogin($pdo, $dbUser['entity_id']);
                    }

                    header("Location: index");
                    exit();
                } else {
                    $error = "Invalid email or password.";
                }
            }
        }

        $pageTitle = 'EasyCart | Login';
        $currentPage = 'login';
        $extraStyles = ['auth.css'];
        $extraScripts = ['auth.js'];
        $signup_success = isset($_GET['registered']) && $_GET['registered'] === 'true';

        $this->render('login', [
            'pageTitle' => $pageTitle,
            'currentPage' => $currentPage,
            'extraStyles' => $extraStyles,
            'extraScripts' => $extraScripts,
            'signup_success' => $signup_success,
            'error' => $error
        ]);
    }
}
