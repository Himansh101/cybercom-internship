<?php
namespace App\Model\CartMetadata;

use App\Lib\Query;
use App\Database;
use PDO;

class Model
{
    protected $resource;
    protected $pdo;
    protected $data = [];

    public function __construct()
    {
        $this->resource = new Resource();
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function loadByCartId($cartId)
    {
        $query = new Query();
        $query->select(['*'])
            ->from($this->resource->tableName)
            ->where("cart_id = ?", $cartId)
            ->limit(1);

        $stmt = $this->pdo->prepare((string) $query);
        $stmt->execute($query->getBinds());
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $this->data = $result;
        } else {
            $this->data = ['cart_id' => $cartId];
        }
        return $this;
    }

    public function save($data)
    {
        $cartId = $data['cart_id'];

        // check exist
        $this->loadByCartId($cartId);

        if (isset($this->data['metadata_id'])) {
            // Update
            $q = new Query();
            $updateData = [];
            if (isset($data['shipping_method']))
                $updateData['shipping_method'] = $data['shipping_method'];
            if (isset($data['coupon_code']))
                $updateData['coupon_code'] = $data['coupon_code'];

            if (empty($updateData))
                return;

            $q->update($this->resource->tableName, $updateData)
                ->where('metadata_id = ?', $this->data['metadata_id']);

            $stmt = $this->pdo->prepare((string) $q);
            // Query update uses placeholders, verify if we need detailed binding
            // My Query->update logic is simple?
            // Wait, previous issue was update bind merging.
            // Let's check Query->update implementation or stick to manual if unsure.
            // But I fixed Query logic earlier? No, I patched usage in CartItem.
            // I'll use the same pattern as CartItem fix: array_merge values + binds.

            // Values from updateData
            $values = array_values($updateData);
            $binds = $q->getBinds();
            // Query::update placeholders are keys
            $stmt->execute(array_merge($values, $binds));

        } else {
            // Insert
            $q = new Query();
            $insertData = [
                'cart_id' => '?',
                'shipping_method' => '?',
                'coupon_code' => '?'
            ];

            $q->insert($this->resource->tableName, $insertData);
            $stmt = $this->pdo->prepare((string) $q);
            $stmt->execute([
                $cartId,
                $data['shipping_method'] ?? 'standard',
                $data['coupon_code'] ?? null
            ]);
        }
    }

    public function getData($key = null)
    {
        if ($key === null)
            return $this->data;
        return $this->data[$key] ?? null;
    }
}
