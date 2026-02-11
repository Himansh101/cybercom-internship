<?php
namespace App\Model\Admin;

use App\Database;
use PDO;

class Statistics
{
    protected $pdo;

    public function __construct()
    {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function getCounts()
    {
        return [
            'products' => $this->pdo->query("SELECT COUNT(*) FROM catalog_product_entity")->fetchColumn(),
            'orders' => $this->pdo->query("SELECT COUNT(*) FROM sales_order")->fetchColumn(),
            'users' => $this->pdo->query("SELECT COUNT(*) FROM customer_entity")->fetchColumn()
        ];
    }
}
