<?php
namespace App\View;

class Order
{
    public function toHtml($template, $data = [])
    {
        extract($data);
        ob_start();

        $pageTitle = $data['pageTitle'] ?? 'EasyCart | Orders';

        if ($template === 'index') {
            // Map variables
            // Legacy view expects $userOrders
            $extraStyles = $data['extraStyles'] ?? [];
            $extraScripts = $data['extraScripts'] ?? [];
            $currentPage = $data['currentPage'] ?? 'orders';

            require __DIR__ . '/../../src/Views/orders.view.php';
        }

        return ob_get_clean();
    }
}
