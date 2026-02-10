<?php
namespace App\Models;

use PDO;
use Exception;

class Order extends BaseModel
{
    public function getHistory($userId)
    {
        $stmt = $this->pdo->prepare("SELECT o.*, 
            i.item_id, i.product_name_snapshot, i.price_snapshot, i.quantity,
            (SELECT image_url FROM catalog_product_image WHERE product_id = i.product_id AND is_main_image = true) as main_image
            FROM sales_order o 
            JOIN sales_order_item i ON o.order_id = i.order_id
            WHERE o.user_id = :u_id 
            ORDER BY o.created_at DESC");
        $stmt->execute([':u_id' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $userOrders = [];
        foreach ($rows as $row) {
            if (!isset($userOrders[$row['order_id']])) {
                $userOrders[$row['order_id']] = [
                    'order_id' => $row['order_number'],
                    'date' => $row['created_at'],
                    'total' => $row['final_amount'],
                    'status' => $row['status'],
                    'shipping_method' => $row['shipping_cost'] > 0 ? 'white_glove' : 'standard',
                    'items' => []
                ];
            }
            $userOrders[$row['order_id']]['items'][] = [
                'name' => $row['product_name_snapshot'],
                'price' => $row['price_snapshot'],
                'qty' => $row['quantity'],
                'image' => $row['main_image']
            ];
        }
        return $userOrders;
    }

    public function getDetails($orderNumber, $userId)
    {
        $stmtOrder = $this->pdo->prepare("SELECT * FROM sales_order WHERE order_number = ? AND user_id = ?");
        $stmtOrder->execute([$orderNumber, $userId]);
        $order = $stmtOrder->fetch(PDO::FETCH_ASSOC);

        if (!$order)
            return null;

        $stmtAddr = $this->pdo->prepare("SELECT * FROM sales_order_address WHERE order_id = ?");
        $stmtAddr->execute([$order['order_id']]);
        $address = $stmtAddr->fetch(PDO::FETCH_ASSOC);

        $stmtItems = $this->pdo->prepare("SELECT i.*, 
            (SELECT image_url FROM catalog_product_image WHERE product_id = i.product_id AND is_main_image = true) as main_image
            FROM sales_order_item i WHERE i.order_id = ?");
        $stmtItems->execute([$order['order_id']]);
        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        $formattedItems = [];
        foreach ($items as $item) {
            $formattedItems[] = [
                'name' => $item['product_name_snapshot'],
                'price' => (float) $item['price_snapshot'],
                'qty' => (int) $item['quantity'],
                'total' => $item['price_snapshot'] * $item['quantity'],
                'image' => $item['main_image']
            ];
        }

        return [
            'order_number' => $order['order_number'],
            'date' => $order['created_at'],
            'status' => $order['status'],
            'subtotal' => (float) $order['subtotal'],
            'shipping' => (float) $order['shipping_cost'],
            'tax' => (float) $order['tax_amount'],
            'total' => (float) $order['final_amount'],
            'address' => $address,
            'items' => $formattedItems
        ];
    }

    public function getLastAddress($userId)
    {
        $stmt = $this->pdo->prepare("SELECT a.* 
            FROM sales_order_address a
            JOIN sales_order o ON a.order_id = o.order_id
            WHERE o.user_id = ?
            ORDER BY o.created_at DESC
            LIMIT 1");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
