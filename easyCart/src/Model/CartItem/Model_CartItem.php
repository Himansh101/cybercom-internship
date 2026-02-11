<?php
namespace App\Model\CartItem;

use App\Lib\Query;
use App\Database;
use PDO;

class Model_CartItem
{
    protected $resource;
    protected $pdo;

    public function __construct()
    {
        $this->resource = new Model_CartItemResource();
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function save($data)
    {
        $cartId = $data['cart_id'];
        $productId = $data['product_id'];
        $qty = $data['quantity'];

        $q = new Query();
        $q->select(['quantity'])
            ->from($this->resource->tableName)
            ->where('cart_id = ?', $cartId)
            ->where('product_id = ?', $productId);

        $stmt = $this->pdo->prepare((string) $q);
        $stmt->execute($q->getBinds());
        $existing = $stmt->fetchColumn();

        if ($existing !== false) {
            $qUp = new Query();
            $qUp->update($this->resource->tableName, ['quantity' => (int) $qty])
                ->where('cart_id = ?', (int) $cartId)
                ->where('product_id = ?', (int) $productId);

            $stmtUp = $this->pdo->prepare((string) $qUp);
            $stmtUp->execute(array_merge([(int) $qty], $qUp->getBinds()));
        } else {
            $qIn = new Query();
            $qIn->insert($this->resource->tableName, [
                'cart_id' => '?',
                'product_id' => '?',
                'quantity' => '?'
            ]);
            $stmtIn = $this->pdo->prepare((string) $qIn);
            $stmtIn->execute([(int) $cartId, (int) $productId, (int) $qty]);
        }
    }

    public function delete($cartId, $productId)
    {
        $q = new Query();
        $q->delete($this->resource->tableName)
            ->where('cart_id = ?', $cartId)
            ->where('product_id = ?', $productId);

        $stmt = $this->pdo->prepare((string) $q);
        $stmt->execute($q->getBinds());
    }
}
