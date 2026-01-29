<?php

/**
 * Centrally manages all shipping calculation logic for EasyCart.
 */
function calculate_shipping_cost($method, $discounted_subtotal)
{
    switch ($method) {
        case 'standard':
            return 40; // Flat $40
        case 'express':
            return min(80, $discounted_subtotal * 0.10); // Flat $80 OR 10% (whichever is lower)
        case 'white_glove':
            return min(150, $discounted_subtotal * 0.05); // Flat $150 OR 5% (whichever is lower)
        case 'freight':
            return max(200, $discounted_subtotal * 0.03); // 3% of subtotal, Minimum $200
        default:
            return 40;
    }
}

/**
 * Returns all available shipping methods and their current costs.
 */
function get_all_shipping_methods($discounted_subtotal)
{
    $methods = ['standard', 'express', 'white_glove', 'freight'];
    $costs = [];
    foreach ($methods as $m) {
        $cost = calculate_shipping_cost($m, $discounted_subtotal);
        $costs[$m] = [
            'value' => $cost,
            'formatted' => '₹' . number_format($cost)
        ];
    }
    return $costs;
}
