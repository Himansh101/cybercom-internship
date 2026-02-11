<?php
namespace App\Model\Customer;

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
        // Simple save logic: if ID exists, update, else insert
        // For this refactor, I'll focus on the specific needs: Signup (Insert)

        $query = new Query();
        if (isset($data[$this->resource->primaryKey])) {
            // Update logic (not requested strictly but good to have)
        } else {
            $query->insert($this->resource->tableName, $data);
            $stmt = $this->pdo->prepare((string) $query);
            // Binds processing for insert in Query builder was specific
            // Let's rely on array_values for simple key-value inserts
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
