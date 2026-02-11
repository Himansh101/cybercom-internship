<?php
namespace App\View;

class View_Checkout
{
    public function toHtml($template, $data = [])
    {
        extract($data);
        ob_start();

        $pageTitle = $data['pageTitle'] ?? 'EasyCart | Checkout';

        if ($template === 'index') {
            // Map 'user' to 'userAddress' for the legacy view
            $userAddress = $data['user'] ?? null;
            require __DIR__ . '/../../src/Views/checkout.view.php';
        }

        return ob_get_clean();
    }
}
