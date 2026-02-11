<?php
namespace App\Controller;

use App\Model\Order\Collection as OrderCollection;
use App\Model\Order\Model as OrderModel;
use App\View\Order as OrderView;

class Order
{
    public function indexAction()
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header("Location: login");
            exit;
        }

        $collection = new OrderCollection();
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
