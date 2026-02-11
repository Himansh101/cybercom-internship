<?php
namespace App\Model\CartMetadata;

use App\Lib\Query;
use App\Database;
use PDO;

class Model_CartMetadata
{
    protected $resource;
    protected $pdo;
    protected $data = [];

    public function __construct()
    {
        $this->resource = new Model_CartMetadataResource();
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function loadByCartId($cartId)
    {
        $q = new Query();
        $q->select(['*'])
            ->from($this->resource->tableName)
            ->where('cart_id = ?', $cartId);

        $stmt = $this->pdo->prepare((string) $q);
        $stmt->execute($q->getBinds());
        $this->data = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        return $this;
    }

    public function save($data)
    {
        $cartId = $data['cart_id'];
        $this->loadByCartId($cartId);

        if (!empty($this->data)) {
            // Update
            $updateData = $data;
            unset($updateData['cart_id']);
            $q = new Query();
            $q->update($this->resource->tableName, $updateData)
                ->where('cart_id = ?', $cartId);
            $stmt = $this->pdo->prepare((string) $q);
            $stmt->execute(array_merge(array_values($updateData), [$cartId]));
        } else {
            // Insert
            $q = new Query();
            $columns = array_keys($data);
            $placeholders = array_fill_keys($columns, '?');
            $q->insert($this->resource->tableName, $placeholders);
            $stmt = $this->pdo->prepare((string) $q);
            $stmt->execute(array_values($data));
        }
    }

    public function getData($key = null)
    {
        if ($key === null)
            return $this->data;
        return $this->data[$key] ?? null;
    }
}
