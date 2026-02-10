<?php
namespace App\Controllers;

class DashboardController extends BaseController
{
    public function index()
    {
        global $pdo, $isLoggedIn, $userId;

        if (!$isLoggedIn) {
            header("Location: login");
            exit();
        }

        // Fetch recent orders etc (Dashboard logic)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM sales_order WHERE user_id = ?");
        $stmt->execute([$userId]);
        $orderCount = $stmt->fetchColumn();

        $pageTitle = 'EasyCart | Dashboard';
        $currentPage = 'dashboard';
        $extraStyles = ['dashboard.css'];
        $extraScripts = ['https://cdn.jsdelivr.net/npm/chart.js', 'dashboard.js'];

        $this->render('dashboard', [
            'orderCount' => $orderCount,
            'pageTitle' => $pageTitle,
            'currentPage' => $currentPage,
            'extraStyles' => $extraStyles,
            'extraScripts' => $extraScripts
        ]);
    }
}
