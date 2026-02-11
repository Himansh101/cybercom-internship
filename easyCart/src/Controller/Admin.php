<?php
namespace App\Controller;

use App\Model\Admin\Statistics;
use App\View\Admin as AdminView;

class Admin
{
    public function indexAction()
    {
        global $isLoggedIn, $user; // From init.php

        // Admin Access Control
        if (!$isLoggedIn) {
            header("Location: login");
            exit();
        }

        if (empty($user['is_admin'])) {
            header("Location: index");
            exit();
        }

        $statsModel = new Statistics();
        $counts = $statsModel->getCounts();

        $view = new AdminView();
        echo $view->toHtml('index', [
            'totalProducts' => $counts['products'],
            'totalOrders' => $counts['orders'],
            'totalUsers' => $counts['users'],
            'pageTitle' => 'EasyCart | Admin Panel',
            'currentPage' => 'admin',
            'extraStyles' => ['admin.css'],
            'extraScripts' => ['admin.js'],
            'isLoggedIn' => $isLoggedIn,
            'cartQuantity' => $GLOBALS['cartQuantity'] ?? 0,
            'user' => $user
        ]);
    }
}
