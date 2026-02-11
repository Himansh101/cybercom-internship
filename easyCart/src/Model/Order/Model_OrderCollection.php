<?php
namespace App\Model\Order;

use App\Lib\Query;
use App\Database;
use PDO;

class Model_OrderCollection
{
    public function getHistoryWithItems($userId)
    {
        $resource = new Model_OrderResource();
        $q = new Query();
        $q->select(['*'])
            ->from($resource->tableName)
            ->where('user_id = ?', $userId)
            ->orderBy('created_at', 'DESC');

        $db = new Database();
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare((string) $q);
        $stmt->execute([$userId]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formattedOrders = [];
        foreach ($orders as $order) {
            $qItems = new Query();
            $qItems->select([
                'i.quantity as qty',
                'i.price_snapshot as price',
                'i.product_name_snapshot as name',
                'p.entity_id as product_id',
                "(SELECT image_url FROM catalog_product_image WHERE product_id = p.entity_id AND is_main_image = true LIMIT 1) as image"
            ])
                ->from('sales_order_item', 'i')
                ->join('catalog_product_entity p', 'i.product_id = p.entity_id', 'LEFT')
                ->where('i.order_id = ?', $order['order_id']);

            $stmtItems = $pdo->prepare((string) $qItems);
            $stmtItems->execute([$order['order_id']]);
            $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

            $formattedOrders[] = [
                'order_id' => $order['order_number'],
                'date' => $order['created_at'],
                'total' => $order['final_amount'],
                'status' => $order['status'],
                'shipping_method' => 'Standard',
                'items' => $items
            ];
        }
        return $formattedOrders;
    }

    public function countByUser($userId)
    {
        $resource = new Model_OrderResource();
        $q = new Query();
        $q->select(['COUNT(*)'])
            ->from($resource->tableName)
            ->where('user_id = ?', $userId);

        $db = new Database();
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare((string) $q);
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }
}
