<?php
namespace App\Model\Order;

use App\Lib\Query;
use App\Database;
use App\Model\OrderItem\Model_OrderItemResource;
use App\Model\OrderAddress\Model_OrderAddressResource;
use PDO;
use Exception;

class Model_Order
{
    protected $resource;
    protected $pdo;

    public function __construct()
    {
        $this->resource = new Model_OrderResource();
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function createOrder($data, $items, $addresses)
    {
        try {
            $this->pdo->beginTransaction();

            $q = new Query();
            $q->insert($this->resource->tableName, $data);
            $stmt = $this->pdo->prepare((string) $q);
            $stmt->execute(array_values($data));
            $orderId = $this->pdo->lastInsertId();

            $itemResource = new Model_OrderItemResource();
            $stmtItem = $this->pdo->prepare("INSERT INTO {$itemResource->tableName} (order_id, product_id, product_name_snapshot, price_snapshot, quantity) VALUES (?, ?, ?, ?, ?)");

            $stmtStock = $this->pdo->prepare("UPDATE catalog_product_entity SET stock_count = stock_count - ? WHERE entity_id = ?");

            foreach ($items as $item) {
                $stmtItem->execute([$orderId, $item['id'], $item['name'], $item['price'], $item['qty']]);
                $stmtStock->execute([$item['qty'], $item['id']]);
            }

            $addrResource = new Model_OrderAddressResource();
            $stmtAddr = $this->pdo->prepare("INSERT INTO {$addrResource->tableName} (order_id, full_name, email, mobile, street_address, city, pincode, address_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            foreach ($addresses as $type => $addr) {
                $stmtAddr->execute([
                    $orderId,
                    $addr['name'],
                    $addr['email'],
                    $addr['mobile'],
                    $addr['address'],
                    $addr['city'],
                    $addr['pincode'],
                    $type
                ]);
            }

            $this->pdo->commit();
            return $orderId;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
