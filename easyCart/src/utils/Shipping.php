<?php
namespace App\Utils;

use PDO;

class Shipping
{
    public static function calculateCost($pdo, $method, $discounted_subtotal)
    {
        $stmt = $pdo->prepare("SELECT type, base_cost, rate_percent, limit_amount FROM sales_shipping_method WHERE code = ? AND is_active = true");
        $stmt->execute([$method]);
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
                $calculated = $discounted_subtotal * ($rule['rate_percent'] / 100);
                $cost = ($rule['limit_amount'] > 0) ? min($rule['limit_amount'], $calculated) : $calculated;
                break;
            case 'percentage_min_floor':
                $calculated = $discounted_subtotal * ($rule['rate_percent'] / 100);
                $cost = max($rule['limit_amount'], $calculated);
                break;
            default:
                $cost = (float) $rule['base_cost'];
        }

        return $cost;
    }

    public static function getAllMethods($pdo, $discounted_subtotal)
    {
        $stmt = $pdo->query("SELECT code, name, description FROM sales_shipping_method WHERE is_active = true ORDER BY sort_order ASC");
        $methods = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $costs = [];
        foreach ($methods as $m) {
            $cost = self::calculateCost($pdo, $m['code'], $discounted_subtotal);
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
