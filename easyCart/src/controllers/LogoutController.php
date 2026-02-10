<?php
namespace App\Controllers;

use Exception;

class LogoutController extends BaseController
{
    public function index()
    {
        global $pdo, $cartId, $cartQuantity;

        // Deactivate cart if it was empty upon logout
        if (isset($cartId) && $cartId && isset($cartQuantity) && $cartQuantity === 0) {
            if (isset($pdo)) {
                try {
                    $stmt = $pdo->prepare("UPDATE sales_cart SET is_active = FALSE WHERE cart_id = ?");
                    $stmt->execute([$cartId]);
                } catch (Exception $e) {
                    error_log("Logout cart deactivation error: " . $e->getMessage());
                }
            }
        }

        session_unset();
        session_destroy();

        $this->render('logout');
    }
}
