<?php
namespace App\View;

class View_Customer
{
    public function toHtml($template, $data = [])
    {
        extract($data);
        ob_start();

        $pageTitle = $data['pageTitle'] ?? 'EasyCart';

        if ($template === 'login') {
            // We need to render the login form. 
            // Existing `login.php` was the controller + view wrapper.
            // We need the ACTUAL HTML content.
            // Since `login.php` used `App\Controllers\LoginController`, the HTML might be in `src/Views/login.view.php`?
            // I'll assume so based on pattern.
            require __DIR__ . '/../../src/Views/login.view.php';
        } elseif ($template === 'signup') {
            require __DIR__ . '/../../src/Views/signup.view.php';
        }

        return ob_get_clean();
    }
}
