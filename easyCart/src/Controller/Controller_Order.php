<?php
namespace App\Controller;

use App\Model\Order\Model_OrderCollection;
use App\Model\Order\Model_Order;
use App\View\View_Order as OrderView;

class Controller_Order
{
    public function indexAction()
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header("Location: login");
            exit;
        }

        $collection = new Model_OrderCollection();
        $orders = $collection->getHistoryWithItems($userId);

        $view = new OrderView();
        echo $view->toHtml('index', [
            'userOrders' => $orders,
            'pageTitle' => 'EasyCart | My Orders',
            'currentPage' => 'orders',
            'extraStyles' => ['orders.css'],
            'extraScripts' => ['orders.js'],
            'isLoggedIn' => $GLOBALS['isLoggedIn'] ?? false,
            'cartQuantity' => $GLOBALS['cartQuantity'] ?? 0,
            'user' => $GLOBALS['user'] ?? null
        ]);
    }

    public function viewAction()
    {
        // Placeholder for future detail view
    }
}
