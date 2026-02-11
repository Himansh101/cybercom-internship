<?php
namespace App\Model\Shipping;

use App\Lib\Query;
use App\Database;
use PDO;

class Model_Shipping
{
    protected $resource;
    protected $pdo;

    public function __construct()
    {
        $this->resource = new Model_ShippingResource();
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function calculateCost($method, $subtotal)
    {
        $query = new Query();
        $query->select(['*'])
            ->from($this->resource->tableName)
            ->where('code = ?', $method)
            ->where('is_active = ?', true);

        $stmt = $this->pdo->prepare((string) $query);
        $stmt->execute([$method, 1]);
        $rule = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$rule) {
            return 40;
        }

        $cost = 0;
        switch ($rule['type']) {
            case 'flat':
                $cost = (float) $rule['base_cost'];
                break;
            case 'percentage_capped':
                $calculated = $subtotal * ($rule['rate_percent'] / 100);
                $cost = ($rule['limit_amount'] > 0) ? min($rule['limit_amount'], $calculated) : $calculated;
                break;
            case 'percentage_min_floor':
                $calculated = $subtotal * ($rule['rate_percent'] / 100);
                $cost = max($rule['limit_amount'], $calculated);
                break;
            default:
                $cost = (float) $rule['base_cost'];
        }
        return $cost;
    }

    public function getAllMethods($subtotal)
    {
        $query = new Query();
        $query->select(['code', 'name', 'description'])
            ->from($this->resource->tableName)
            ->where('is_active = ?', true)
            ->orderBy('sort_order', 'ASC');

        $stmt = $this->pdo->prepare((string) $query);
        $stmt->execute([1]);
        $methods = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $costs = [];
        foreach ($methods as $m) {
            $cost = $this->calculateCost($m['code'], $subtotal);
            $costs[$m['code']] = [
                'value' => $cost,
                'formatted' => '₹' . number_format($cost),
                'name' => $m['name'],
                'description' => $m['description']
            ];
        }
        return $costs;
    }
}
