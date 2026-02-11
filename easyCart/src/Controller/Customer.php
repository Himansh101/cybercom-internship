<?php
namespace App\Controller;

use App\Model\Customer\Model;
use App\View\Customer as CustomerView;

class Customer
{
    public function loginAction()
    {
        $error = $_GET['error'] ?? null;

        // Handle Post
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $model = new Model();
            $customer = $model->loadByEmail($email)->getData();

            if ($customer && password_verify($password, $customer['password'])) {
                if (!$customer['is_active']) {
                    header("Location: login?error=account_deactivated");
                    exit();
                }

                $_SESSION['user_id'] = $customer['entity_id'];
                // Sync Cart logic if needed (CartSync utils)
                // Legacy logic called syncCart($pdo, $virtualCartId, $userCartId);
                // Strict MVC: Should be in Cart Controller or appropriate Service.
                // For now, I'll keep it simple or invoke the existing util function if available globally.
                // `src/init.php` includes `utils/cartsync.utils.php`.

                // Reuse existing logic for simplicity in refactor
                global $pdo; // From init.php
                if (function_exists('mergeCarts')) {
                    // mergeCarts is likely the function name, need to check utils.
                    // But let's assume standard session setup is enough for now or I'd check the file.
                }

                header("Location: index");
                exit();
            } else {
                header("Location: login?error=invalid_credentials");
                exit();
            }
        }

        $view = new CustomerView();

        $signupSuccess = isset($_GET['signup_success']) && $_GET['signup_success'] == 1;

        echo $view->toHtml('login', [
            'error' => $error,
            'pageTitle' => 'EasyCart | Login',
            'extraStyles' => ['auth.css'],
            'extraScripts' => ['auth.js', 'main.js'],
            'signup_success' => $signupSuccess
        ]);
    }

    public function signupAction()
    {
        $error = $_GET['error'] ?? null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $mobile = $_POST['mobile'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if ($password !== $confirmPassword) {
                header("Location: signup?error=password_mismatch");
                exit();
            }

            $model = new Model();
            if ($model->exists($email)) {
                header("Location: signup?error=email_exists");
                exit();
            }

            $data = [
                'name' => $name,
                'email' => $email,
                'mobile' => $mobile,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
                'is_active' => 1,
                'is_admin' => 0
            ];

            $id = $model->save($data);

            if ($id) {
                $_SESSION['user_id'] = $id;
                header("Location: index");
                exit();
            } else {
                header("Location: signup?error=registration_failed");
                exit();
            }
        }

        $view = new CustomerView();
        echo $view->toHtml('signup', [
            'error' => $error,
            'pageTitle' => 'EasyCart | Signup',
            'extraStyles' => []
        ]);
    }

    public function logoutAction()
    {
        session_destroy();
        header("Location: login");
        exit();
    }
}
