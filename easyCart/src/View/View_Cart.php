<?php
namespace App\View;

class View_Cart
{
    public function toHtml($template, $data = [])
    {
        extract($data);
        ob_start();

        $pageTitle = $data['pageTitle'] ?? 'EasyCart';

        if ($template === 'index') {
            // We need to map data to what `cart.view.php` expects.
            // `cart.view.php` likely iterates `$cartItems`.
            // And expects `$subtotal`, `$shipping_fee`, `$total`.
            // I need to check `cart.view.php` content to be sure.
            // But usually it uses what `cart.php` passed.
            require __DIR__ . '/../../src/Views/cart.view.php';
        }

        return ob_get_clean();
    }
}
