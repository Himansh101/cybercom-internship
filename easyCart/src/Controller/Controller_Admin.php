<?php
namespace App\Controller;

use App\Model\Admin\Model_AdminStatistics;
use App\View\View_Admin as AdminView;

class Controller_Admin
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

        $statsModel = new Model_AdminStatistics();
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
