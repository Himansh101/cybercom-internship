<?php
namespace App\Controllers;

class BaseController
{
    /**
     * Render a view file and pass data to it.
     */
    protected function render($view, $data = [])
    {
        // Access global variables from init.php
        global $isLoggedIn, $cartQuantity, $user, $userId, $cartId, $pdo;

        // Merge globals with passed data (data takes precedence)
        $viewData = array_merge([
            'isLoggedIn' => $isLoggedIn,
            'cartQuantity' => $cartQuantity,
            'user' => $user,
            'userId' => $userId,
            'cartId' => $cartId,
            'pdo' => $pdo
        ], $data);

        // Extract data to make variables available in the view
        extract($viewData);

        // Build path to view
        $viewPath = __DIR__ . '/../views/' . $view . '.view.php';

        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            die("View $view not found at $viewPath");
        }
    }
}
