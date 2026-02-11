<?php
namespace App\Controller;

use App\Model\Order\Collection as OrderCollection;
use App\View\Dashboard as DashboardView;

class Dashboard
{
    public function indexAction()
    {
        global $isLoggedIn, $userId;

        if (!$isLoggedIn) {
            header("Location: login");
            exit();
        }

        $orderCollection = new OrderCollection();
        $orderCount = $orderCollection->countByUser($userId);

        $view = new DashboardView();
        echo $view->toHtml('index', [
            'orderCount' => $orderCount,
            'pageTitle' => 'EasyCart | Dashboard',
            'currentPage' => 'dashboard',
            'extraStyles' => ['dashboard.css'],
            'extraScripts' => ['https://cdn.jsdelivr.net/npm/chart.js', 'dashboard.js'],
            'isLoggedIn' => $isLoggedIn,
            'cartQuantity' => $GLOBALS['cartQuantity'] ?? 0,
            'user' => $GLOBALS['user'] ?? null
        ]);
    }
}
