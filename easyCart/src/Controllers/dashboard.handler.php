<?php
require_once __DIR__ . '/../init.php';

header('Content-Type: application/json');

// Authentication Check
if (!$isLoggedIn) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$userId = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'get_stats') {
    try {
        // 1. Fetch Summary Metrics
        $stmtMetrics = $pdo->prepare("SELECT COUNT(*) as total_orders, SUM(final_amount) as total_spent FROM sales_order WHERE user_id = ?");
        $stmtMetrics->execute([$userId]);
        $metrics = $stmtMetrics->fetch(PDO::FETCH_ASSOC);

        // 2. Fetch Chart Data (Spending Trends)
        $stmtChart = $pdo->prepare("SELECT DATE(created_at) as order_date, SUM(final_amount) as daily_amount 
                                    FROM sales_order 
                                    WHERE user_id = ? 
                                    GROUP BY DATE(created_at) 
                                    ORDER BY order_date ASC");
        $stmtChart->execute([$userId]);
        $chartDataRaw = $stmtChart->fetchAll(PDO::FETCH_ASSOC);

        $chartLabels = [];
        $chartValues = [];
        foreach ($chartDataRaw as $row) {
            $chartLabels[] = date('d M', strtotime($row['order_date']));
            $chartValues[] = (float)$row['daily_amount'];
        }

        echo json_encode([
            'status' => 'success',
            'data' => [
                'total_orders' => number_format((int)($metrics['total_orders'] ?? 0)),
                'total_spent' => number_format((float)($metrics['total_spent'] ?? 0)),
                'chart_labels' => $chartLabels,
                'chart_values' => $chartValues
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
