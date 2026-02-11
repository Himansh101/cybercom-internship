<?php
namespace App\Model\Customer;

use App\Lib\Query;
use App\Database;
use PDO;

class Model_Customer
{
    protected $resource;
    protected $pdo;
    protected $data = [];

    public function __construct()
    {
        $this->resource = new Model_CustomerResource();
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

    public function loadByEmail($email)
    {
        $query = new Query();
        $query->select(['*'])
            ->from($this->resource->tableName)
            ->where("email = ?", $email);

        $stmt = $this->pdo->prepare((string) $query);
        $stmt->execute($query->getBinds());
        $this->data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $this;
    }

    public function save($data)
    {
        $query = new Query();
        if (isset($data[$this->resource->primaryKey])) {
            // Update logic if needed
            return null;
        } else {
            $query->insert($this->resource->tableName, $data);
            $stmt = $this->pdo->prepare((string) $query);
            $stmt->execute(array_values($data));
            return $this->pdo->lastInsertId();
        }
    }

    public function getData($key = null)
    {
        if ($key === null)
            return $this->data;
        return $this->data[$key] ?? null;
    }

    public function exists($email)
    {
        $query = new Query();
        $query->select(['COUNT(*)'])
            ->from($this->resource->tableName)
            ->where("email = ?", $email);

        $stmt = $this->pdo->prepare((string) $query);
        $stmt->execute($query->getBinds());
        return $stmt->fetchColumn() > 0;
    }
}
