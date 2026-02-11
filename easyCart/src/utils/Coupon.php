<?php
namespace App\Utils;

use PDO;

class Coupon
{
    public static function getData($pdo, $coupon_code, $subtotal)
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

        $stmt = $pdo->prepare("SELECT discount_percent, description FROM sales_coupon WHERE code = ? AND is_active = true");
        $stmt->execute([$coupon_code_upper]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($coupon) {
            $data['valid'] = true;
            $data['discount_pct'] = (float) $coupon['discount_percent'];
            $data['discount_amount'] = $subtotal * ($data['discount_pct'] / 100);
            $data['message'] = $coupon['description'];
        } else {
            $data['message'] = 'Invalid coupon code';
        }

        return $data;
    }
}
