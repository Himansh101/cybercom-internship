<?php
namespace App\Utils;

use PDO;
use Exception;

class Cart
{
    public static function getOrCreateId($pdo, $userId = null, $createIfMissing = false)
    {
        $sessionId = session_id();
        if (!$sessionId)
            return null;

        if (isset($_SESSION['cart_id'])) {
            $id = $_SESSION['cart_id'];
            $stmt = $pdo->prepare("SELECT cart_id FROM sales_cart WHERE cart_id = ? AND is_active = TRUE");
            $stmt->execute([$id]);
            if ($stmt->fetch()) {
                return (int) $id;
            } else {
                unset($_SESSION['cart_id']);
            }
        }

        if ($userId) {
            $stmt = $pdo->prepare("SELECT cart_id FROM sales_cart WHERE user_id = ? AND is_active = TRUE");
            $stmt->execute([$userId]);
            $id = $stmt->fetchColumn();
            if ($id) {
                $_SESSION['cart_id'] = $id;
                return (int) $id;
            }
        }

        $stmt = $pdo->prepare("SELECT cart_id FROM sales_cart WHERE session_id = ? AND user_id IS NULL AND is_active = TRUE");
        $stmt->execute([$sessionId]);
        $id = $stmt->fetchColumn();
        if ($id) {
            $_SESSION['cart_id'] = $id;
            return (int) $id;
        }

        if (!$createIfMissing) {
            return null;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO sales_cart (session_id, user_id) VALUES (?, ?) RETURNING cart_id");
            $stmt->execute([$sessionId, $userId]);
            $id = $stmt->fetchColumn();
            $_SESSION['cart_id'] = $id;
            return (int) $id;
        } catch (Exception $e) {
            error_log("Cart creation error: " . $e->getMessage());
            return null;
        }
    }

    public static function updateItem($pdo, $cartId, $productId, $quantity)
    {
        if (!$cartId)
            return false;

        try {
            if ($quantity <= 0) {
                $stmt = $pdo->prepare("DELETE FROM sales_cart_product WHERE cart_id = ? AND product_id = ?");
                $stmt->execute([$cartId, $productId]);
            } else {
                $check = $pdo->prepare("SELECT increment_id FROM sales_cart_product WHERE cart_id = ? AND product_id = ?");
                $check->execute([$cartId, $productId]);
                if ($check->fetch()) {
                    $stmt = $pdo->prepare("UPDATE sales_cart_product SET quantity = ? WHERE cart_id = ? AND product_id = ?");
                    $stmt->execute([$quantity, $cartId, $productId]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO sales_cart_product (cart_id, product_id, quantity) VALUES (?, ?, ?)");
                    $stmt->execute([$cartId, $productId, $quantity]);
                }
            }
            return true;
        } catch (Exception $e) {
            error_log("Cart item update error: " . $e->getMessage());
            return false;
        }
    }

    public static function loadArray($pdo, $cartId)
    {
        if (!$cartId)
            return [];

        $cart = [];
        $stmt = $pdo->prepare("SELECT product_id, quantity FROM sales_cart_product WHERE cart_id = ?");
        $stmt->execute([$cartId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $cart[$row['product_id']] = (int) $row['quantity'];
        }
        return $cart;
    }

    public static function mergeOnLogin($pdo, $userId)
    {
        $sessionId = session_id();

        $stmt = $pdo->prepare("SELECT cart_id FROM sales_cart WHERE user_id = ? AND is_active = TRUE");
        $stmt->execute([$userId]);
        $userCartId = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT cart_id FROM sales_cart WHERE session_id = ? AND user_id IS NULL AND is_active = TRUE");
        $stmt->execute([$sessionId]);
        $guestCartId = $stmt->fetchColumn();

        if (!$userCartId) {
            if ($guestCartId) {
                $pdo->prepare("UPDATE sales_cart SET user_id = ? WHERE cart_id = ?")->execute([$userId, $guestCartId]);
                $_SESSION['cart_id'] = $guestCartId;
                return $guestCartId;
            } else {
                return self::getOrCreateId($pdo, $userId);
            }
        }

        if ($guestCartId && $guestCartId != $userCartId) {
            $stmt = $pdo->prepare("SELECT product_id, quantity FROM sales_cart_product WHERE cart_id = ?");
            $stmt->execute([$guestCartId]);
            $guestItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($guestItems as $item) {
                $check = $pdo->prepare("SELECT increment_id, quantity FROM sales_cart_product WHERE cart_id = ? AND product_id = ?");
                $check->execute([$userCartId, $item['product_id']]);
                $existing = $check->fetch(PDO::FETCH_ASSOC);

                if ($existing) {
                    $newQty = $existing['quantity'] + $item['quantity'];
                    $pdo->prepare("UPDATE sales_cart_product SET quantity = ? WHERE increment_id = ?")->execute([$newQty, $existing['increment_id']]);
                } else {
                    $pdo->prepare("INSERT INTO sales_cart_product (cart_id, product_id, quantity) VALUES (?, ?, ?)")
                        ->execute([$userCartId, $item['product_id'], $item['quantity']]);
                }
            }
            $pdo->prepare("DELETE FROM sales_cart WHERE cart_id = ?")->execute([$guestCartId]);
        }

        $_SESSION['cart_id'] = $userCartId;
        return $userCartId;
    }

    public static function getMetadata($pdo, $cartId)
    {
        if (!$cartId)
            return ['shipping_method' => 'standard', 'coupon_code' => '', 'payment_method' => 'cod'];

        $stmt = $pdo->prepare("SELECT shipping_method, coupon_code, payment_method FROM sales_cart_metadata WHERE cart_id = ?");
        $stmt->execute([$cartId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ?: ['shipping_method' => 'standard', 'coupon_code' => '', 'payment_method' => 'cod'];
    }

    public static function updateMetadata($pdo, $cartId, $data)
    {
        if (!$cartId)
            return false;

        $shipping = $data['shipping_method'] ?? 'standard';
        $coupon = $data['coupon_code'] ?? null;
        $payment = $data['payment_method'] ?? 'cod';

        try {
            $check = $pdo->prepare("SELECT metadata_id FROM sales_cart_metadata WHERE cart_id = ?");
            $check->execute([$cartId]);
            if ($check->fetch()) {
                $stmt = $pdo->prepare("UPDATE sales_cart_metadata SET shipping_method = ?, coupon_code = ?, payment_method = ? WHERE cart_id = ?");
                $stmt->execute([$shipping, $coupon, $payment, $cartId]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO sales_cart_metadata (cart_id, shipping_method, coupon_code, payment_method) VALUES (?, ?, ?, ?)");
                $stmt->execute([$cartId, $shipping, $coupon, $payment]);
            }
            return true;
        } catch (Exception $e) {
            error_log("Cart metadata update error: " . $e->getMessage());
            return false;
        }
    }

    public static function clearMetadata($pdo, $cartId)
    {
        if (!$cartId)
            return;
        $pdo->prepare("DELETE FROM sales_cart_metadata WHERE cart_id = ?")->execute([$cartId]);
    }
}
