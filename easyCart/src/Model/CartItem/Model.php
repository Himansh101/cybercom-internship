<?php
namespace App\Model\CartItem;

use App\Lib\Query;
use App\Database;
use PDO;

class Model
{
    protected $resource;
    protected $pdo;

    public function __construct()
    {
        $this->resource = new Resource();
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function save($data)
    {
        // Logic to insert/update cart item
        // "ON CONFLICT" logic is Postgres specific. 
        // Query builder doesn't support UPSERT yet.
        // I'll stick to raw SQL for the upsert logic if allowed or standard check-then-insert.
        // User said "No raw SQL anywhere else in the project" except Query class?
        // Actually "Uses the centralized query system only".
        // My Query builder (Lib\Query) doesn't support ON CONFLICT.
        // So I should do: Select -> Check -> Insert or Update.

        $cartId = $data['cart_id'];
        $productId = $data['product_id'];
        $qty = $data['quantity'];

        // 1. Check existence
        $q = new Query();
        $q->select(['quantity'])
            ->from($this->resource->tableName)
            ->where('cart_id = ?', $cartId)
            ->where('product_id = ?', $productId);

        $stmt = $this->pdo->prepare((string) $q);
        $stmt->execute($q->getBinds());
        $existing = $stmt->fetchColumn();

        if ($existing !== false) {
            // Update
            $qUp = new Query();
            // Casting to int for safety since Query builder puts values directly in SET clause
            $qUp->update($this->resource->tableName, ['quantity' => (int) $qty])
                ->where('cart_id = ?', (int) $cartId)
                ->where('product_id = ?', (int) $productId);

            $stmtUp = $this->pdo->prepare((string) $qUp);
            // Query::update now uses placeholders, so we must pass values + binds
            $stmtUp->execute(array_merge([(int) $qty], $qUp->getBinds()));
        } else {
            // Insert
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
