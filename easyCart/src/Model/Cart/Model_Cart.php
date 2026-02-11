<?php
namespace App\Model\Cart;

use App\Lib\Query;
use App\Database;
use PDO;

class Model_Cart
{
    protected $resource;
    protected $pdo;
    public $data = [];

    public function __construct()
    {
        $this->resource = new Model_CartResource();
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function load($id)
    {
        $query = new Query();
        $query->select(['*'])
            ->from($this->resource->tableName)
            ->where("{$this->resource->primaryKey} = ?", $id);

        $stmt = $this->pdo->prepare((string) $query);
        $stmt->execute($query->getBinds());
        $this->data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $this;
    }

    public function loadByUserId($userId)
    {
        $query = new Query();
        $query->select(['*'])
            ->from($this->resource->tableName)
            ->where("user_id = ?", $userId)
            ->where("is_active = ?", true)
            ->limit(1);

        $stmt = $this->pdo->prepare((string) $query);
        $stmt->execute($query->getBinds());
        $this->data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $this;
    }

    public function save($data)
    {
        if (isset($data['entity_id'])) {
            // Update logic if needed
            return null;
        } else {
            $q = new Query();
            $columns = array_keys($data);
            $placeholders = array_fill_keys($columns, '?');

            $q->insert($this->resource->tableName, $placeholders);
            $stmt = $this->pdo->prepare((string) $q);
            $stmt->execute(array_values($data));
            return $this->pdo->lastInsertId();
        }
    }

    public function getId()
    {
        return $this->data[$this->resource->primaryKey] ?? null;
    }
}
