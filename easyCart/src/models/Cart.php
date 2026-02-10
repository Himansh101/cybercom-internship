<?php
namespace App\Models;

use PDO;

class Cart extends BaseModel
{
    public function getItems($cartId)
    {
        if (!$cartId)
            return [];

        // Use the existing utility function for now, or migrate logic later
        if (function_exists('loadCartArrayFromDb')) {
            return loadCartArrayFromDb($this->pdo, $cartId);
        }

        $stmt = $this->pdo->prepare("SELECT product_id, quantity FROM sales_cart_product WHERE cart_id = ?");
        $stmt->execute([$cartId]);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function updateItem($cartId, $productId, $quantity)
    {
        if (function_exists('updateCartItemDb')) {
            return updateCartItemDb($this->pdo, $cartId, $productId, $quantity);
        }

        if ($quantity <= 0) {
            $stmt = $this->pdo->prepare("DELETE FROM sales_cart_product WHERE cart_id = ? AND product_id = ?");
            return $stmt->execute([$cartId, $productId]);
        }

        $stmt = $this->pdo->prepare("INSERT INTO sales_cart_product (cart_id, product_id, quantity) VALUES (?, ?, ?)
                                    ON CONFLICT (cart_id, product_id) DO UPDATE SET quantity = EXCLUDED.quantity");
        return $stmt->execute([$cartId, $productId, $quantity]);
    }

    public function getOrCreate($userId)
    {
        if (function_exists('getOrCreateCartId')) {
            return getOrCreateCartId($this->pdo, $userId, true);
        }
        // Logic for creating cart if missing...
    }
}
