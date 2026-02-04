<?php

/**
 * Centrally manages all shipping calculation logic for EasyCart.
 */
/**
 * Centrally manages all shipping calculation logic for EasyCart.
 * Fetches rules from sales_shipping_method table.
 */
function calculate_shipping_cost($pdo, $method, $discounted_subtotal)
{
    $stmt = $pdo->prepare("SELECT type, base_cost, rate_percent, limit_amount FROM sales_shipping_method WHERE code = ? AND is_active = true");
    $stmt->execute([$method]);
    $rule = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rule) {
        // Fallback or default if method not found / inactive
        return 40; 
    }

    $cost = 0;
    switch ($rule['type']) {
        case 'flat':
            $cost = (float)$rule['base_cost'];
            break;
        case 'percentage_capped':
            // e.g. 10% capped at 80. logic: min(cap, subtotal * %)
            $calculated = $discounted_subtotal * ($rule['rate_percent'] / 100);
            $cost = ($rule['limit_amount'] > 0) ? min($rule['limit_amount'], $calculated) : $calculated;
            break;
        case 'percentage_min_floor':
            // e.g. 3% but at least 200. logic: max(floor, subtotal * %)
            $calculated = $discounted_subtotal * ($rule['rate_percent'] / 100);
            $cost = max($rule['limit_amount'], $calculated);
            break;
        default:
            $cost = (float)$rule['base_cost'];
    }

    return $cost;
}

/**
 * Returns all available shipping methods and their current costs.
 */
function get_all_shipping_methods($pdo, $discounted_subtotal)
{
    $stmt = $pdo->query("SELECT code, name, description FROM sales_shipping_method WHERE is_active = true ORDER BY sort_order ASC");
    $methods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $costs = [];
    foreach ($methods as $m) {
        $cost = calculate_shipping_cost($pdo, $m['code'], $discounted_subtotal);
        $costs[$m['code']] = [
            'value' => $cost,
            'formatted' => '₹' . number_format($cost),
            'name' => $m['name'],
            'description' => $m['description']
        ];
    }
    return $costs;
}
