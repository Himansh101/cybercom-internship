<?php
namespace App\Controllers;

use App\Models\Cart;
use App\Models\Product;

class CartController extends BaseController
{
    public function index()
    {
        global $pdo, $cartId;

        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header("Location: login");
            exit();
        }

        $cartModel = new Cart();
        $productModel = new Product();

        $subtotal = 0;
        $hasFreightItem = false;

        $itemsFromDb = $cartModel->getItems($cartId);
        $cartItems = [];

        if (!empty($itemsFromDb)) {
            foreach ($itemsFromDb as $id => $quantity) {
                $product = $productModel->findById($id);

                if ($product) {
                    $item_total = $product['price'] * $quantity;
                    $subtotal += $item_total;
                    if ($product['item_shipping_type'] === 'freight') {
                        $hasFreightItem = true;
                    }

                    $cartItems[$id] = [
                        'id' => $product['id'],
                        'name' => $product['name'],
                        'price' => $product['price'],
                        'image' => $product['image'],
                        'stock_count' => $product['stock_count'],
                        'quantity' => $quantity,
                        'item_total' => $item_total
                    ];
                } else {
                    // Product no longer exists, remove from DB
                    $cartModel->updateItem($cartId, $id, 0);
                }
            }
        }

        // Determine default shipping method
        if ($hasFreightItem || $subtotal > 300) {
            $defaultShippingMethod = 'white_glove';
        } else {
            $defaultShippingMethod = 'standard';
        }

        // Calculate shipping cost (utils call for now)
        $shipping_fee = function_exists('calculate_shipping_cost')
            ? calculate_shipping_cost($pdo, $defaultShippingMethod, $subtotal)
            : 0;

        $pageTitle = 'EasyCart | Shopping Cart';
        $currentPage = 'cart';
        $extraStyles = ['cart.css'];
        $extraScripts = ['cart.js'];

        $this->render('cart', [
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'shipping_fee' => $shipping_fee,
            'defaultShippingMethod' => $defaultShippingMethod,
            'pageTitle' => $pageTitle,
            'currentPage' => $currentPage,
            'extraStyles' => $extraStyles,
            'extraScripts' => $extraScripts
        ]);
    }
}
