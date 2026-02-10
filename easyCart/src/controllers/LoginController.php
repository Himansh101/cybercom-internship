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
                    // Check if account is active
                    if (isset($dbUser['is_active']) && $dbUser['is_active'] === false) {
                        $error = "Your account is deactivated. Please contact support.";
                    } else {
                        $_SESSION['user_id'] = $dbUser['entity_id'];

                        // Merge cart items
                        if (function_exists('mergeCartOnLogin')) {
                            $_SESSION['cart_id'] = mergeCartOnLogin($pdo, $dbUser['entity_id']);
                        }

                        $redirect = $_GET['redirect'] ?? 'index';
                        // Basic security: don't allow absolute URLs to prevent open redirect
                        if (strpos($redirect, 'http') === 0 || strpos($redirect, '//') === 0) {
                            $redirect = 'index';
                        }

                        header("Location: " . $redirect);
                        exit();
                    }
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
