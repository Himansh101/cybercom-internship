<?php
namespace App\Controllers;

use App\Models\Cart;
use App\Models\Product;

class CheckoutController extends BaseController
{
    public function index()
    {
        global $pdo, $cartId, $userId;

        /* 
        if (!isset($_SESSION['user_id'])) {
            header("Location: login");
            exit();
        }
        */

        $cartModel = new Cart();
        $productModel = new Product();

        $cartItems = $cartModel->getItems($cartId);
        if (empty($cartItems)) {
            header("Location: cart");
            exit();
        }

        $subtotal = 0;
        $hasFreightItem = false;
        $processedItems = [];

        foreach ($cartItems as $pid => $qty) {
            $product = $productModel->findById($pid);
            if ($product) {
                $subtotal += $product['price'] * $qty;
                if ($product['item_shipping_type'] === 'freight') {
                    $hasFreightItem = true;
                }
                $processedItems[] = array_merge($product, [
                    'quantity' => $qty,
                    'item_total' => $product['price'] * $qty
                ]);
            }
        }

        $orderModel = new \App\Models\Order();
        $userAddress = $orderModel->getLastAddress($userId);

        // Coupon Logic
        $coupon_code = $_SESSION['coupon_code'] ?? '';
        $coupon_data = get_coupon_data($pdo, $coupon_code, $subtotal);
        $discount = $coupon_data['discount_amount'];
        $discount_message = $coupon_data['message'];
        $discount_percentage = $coupon_data['discount_pct'];

        $discounted_subtotal = $subtotal - $discount;
        $gst = round($discounted_subtotal * 0.18, 2);

        // Determine default shipping method
        $defaultShippingMethod = ($hasFreightItem || $subtotal > 300) ? 'white_glove' : 'standard';
        $shipping = calculate_shipping_cost($pdo, $defaultShippingMethod, $discounted_subtotal);

        $final_total = $discounted_subtotal + $gst + $shipping;

        // Shipping Methods
        $allowedShippingMethods = ($hasFreightItem || $subtotal > 300) ? ['white_glove', 'next_day'] : ['standard', 'express', 'white_glove'];
        $method = $defaultShippingMethod;
        $saved_payment_method = $_SESSION['payment_method'] ?? 'cod';

        $pageTitle = 'EasyCart | Checkout';
        $currentPage = 'checkout';
        $extraStyles = ['cart.css'];
        $extraScripts = ['checkout.js'];

        $this->render('checkout', [
            'checkoutItems' => $processedItems,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'discount_message' => $discount_message,
            'discount_percentage' => $discount_percentage,
            'coupon_code' => $coupon_code,
            'gst' => $gst,
            'shipping' => $shipping,
            'final_total' => $final_total,
            'userAddress' => $userAddress,
            'hasFreightItem' => $hasFreightItem,
            'allowedShippingMethods' => $allowedShippingMethods,
            'method' => $method,
            'saved_payment_method' => $saved_payment_method,
            'pageTitle' => $pageTitle,
            'currentPage' => $currentPage,
            'extraStyles' => $extraStyles,
            'extraScripts' => $extraScripts
        ]);
    }
}
