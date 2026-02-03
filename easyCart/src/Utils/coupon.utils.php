<?php

/**
 * Centrally manages all coupon logic for EasyCart.
 * Returns an array with validation status, discount percentage, and message.
 */
function get_coupon_data($coupon_code, $subtotal)
{
    $coupon_code_upper = strtoupper(trim($coupon_code));
    $data = [
        'valid' => false,
        'discount_pct' => 0,
        'discount_amount' => 0,
        'message' => ''
    ];

    if (empty($coupon_code_upper)) {
        return $data;
    }

    switch ($coupon_code_upper) {
        case 'SAVE5':
            $data['discount_pct'] = 5;
            $data['valid'] = true;
            break;
        case 'SAVE10':
            $data['discount_pct'] = 10;
            $data['valid'] = true;
            break;
        case 'SAVE15':
            $data['discount_pct'] = 15;
            $data['valid'] = true;
            break;
        case 'SAVE20':
            $data['discount_pct'] = 20;
            $data['valid'] = true;
            break;
        default:
            $data['message'] = 'Invalid coupon code';
            return $data;
    }

    $data['discount_amount'] = $subtotal * ($data['discount_pct'] / 100);
    $data['message'] = $data['discount_pct'] . '% discount applied!';

    return $data;
}
