<?php
namespace App\Model\Customer;

use App\Lib\Query;
use App\Database;
use PDO;

class Model_CustomerCollection
{
    protected $resource;
    protected $pdo;
    protected $query;

    public function __construct()
    {
        $this->resource = new Model_CustomerResource();
        $db = new Database();
        $this->pdo = $db->getConnection();
        $this->query = new Query();
        $this->query->select(['*'])->from($this->resource->tableName);
    }

    public function getItems()
    {
        $stmt = $this->pdo->prepare((string) $this->query);
        $stmt->execute($this->query->getBinds());
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
