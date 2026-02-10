<?php
namespace App\Controllers;

use App\Models\Order;

class OrderController extends BaseController
{
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login");
            exit();
        }

        $userId = $_SESSION['user_id'];
        $orderModel = new Order();
        $userOrders = $orderModel->getHistory($userId);

        $pageTitle = 'EasyCart | My Orders';
        $currentPage = 'orders';
        $extraStyles = ['orders.css'];
        $extraScripts = ['checkout.js', 'orders.js'];

        $this->render('orders', [
            'userOrders' => $userOrders,
            'pageTitle' => $pageTitle,
            'currentPage' => $currentPage,
            'extraStyles' => $extraStyles,
            'extraScripts' => $extraScripts
        ]);
    }
}
