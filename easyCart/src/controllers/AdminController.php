<?php
namespace App\Controllers;

class AdminController extends BaseController
{
    public function index()
    {
        global $pdo, $isLoggedIn, $user;

        // Admin Access Control
        if (!$isLoggedIn) {
            header("Location: login");
            exit();
        }

        if (!$user['is_admin']) {
            header("Location: index");
            exit();
        }

        // Fetch summary stats
        $totalProducts = $pdo->query("SELECT COUNT(*) FROM catalog_product_entity")->fetchColumn();
        $totalOrders = $pdo->query("SELECT COUNT(*) FROM sales_order")->fetchColumn();
        $totalUsers = $pdo->query("SELECT COUNT(*) FROM customer_entity")->fetchColumn();

        $pageTitle = 'EasyCart | Admin Panel';
        $currentPage = 'admin';
        $extraStyles = ['admin.css'];
        $extraScripts = ['admin.js'];

        $this->render('admin', [
            'totalProducts' => $totalProducts,
            'totalOrders' => $totalOrders,
            'totalUsers' => $totalUsers,
            'pageTitle' => $pageTitle,
            'currentPage' => $currentPage,
            'extraStyles' => $extraStyles,
            'extraScripts' => $extraScripts
        ]);
    }
}
