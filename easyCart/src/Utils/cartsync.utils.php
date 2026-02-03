<?php

/**
 * Gets or creates a cart record and returns the cart_id.
 * If userId is provided, it attempts to find/link the cart.
 */
function getOrCreateCartId($pdo, $userId = null)
{
    $sessionId = session_id();
    if (!$sessionId) return null;

    // 1. Check session first
    if (isset($_SESSION['cart_id'])) {
        return $_SESSION['cart_id'];
    }

    // 2. Check DB by user_id if logged in
    if ($userId) {
        $stmt = $pdo->prepare("SELECT cart_id FROM sales_cart WHERE user_id = ?");
        $stmt->execute([$userId]);
        $id = $stmt->fetchColumn();
        if ($id) {
            $_SESSION['cart_id'] = $id;
            return $id;
        }
    }

    // 3. Check DB by session_id for guest
    $stmt = $pdo->prepare("SELECT cart_id FROM sales_cart WHERE session_id = ? AND user_id IS NULL");
    $stmt->execute([$sessionId]);
    $id = $stmt->fetchColumn();
    if ($id) {
        $_SESSION['cart_id'] = $id;
        return $id;
    }

    // 4. Create new cart
    try {
        $stmt = $pdo->prepare("INSERT INTO sales_cart (session_id, user_id) VALUES (?, ?) RETURNING cart_id");
        $stmt->execute([$sessionId, $userId]);
        $id = $stmt->fetchColumn();
        $_SESSION['cart_id'] = $id;
        return $id;
    } catch (Exception $e) {
        error_log("Cart creation error: " . $e->getMessage());
        return null;
    }
}

/**
 * Updates or adds an item in the database cart.
 */
function updateCartItemDb($pdo, $cartId, $productId, $quantity)
{
    if (!$cartId) return false;

    try {
        if ($quantity <= 0) {
            $stmt = $pdo->prepare("DELETE FROM sales_cart_product WHERE cart_id = ? AND product_id = ?");
            $stmt->execute([$cartId, $productId]);
        } else {
            // Upsert logic (PostgreSQL style)
            $stmt = $pdo->prepare("INSERT INTO sales_cart_product (cart_id, product_id, quantity) 
                                   VALUES (?, ?, ?) 
                                   ON CONFLICT (cart_id, product_id) DO UPDATE SET quantity = EXCLUDED.quantity");
            // Wait, does sales_cart_product have a unique constraint on (cart_id, product_id)?
            // Looking at schema.sql, it doesn't. Let's do it manually or add the constraint.
            
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

/**
 * Loads the user's cart from the database into an associative array.
 */
function loadCartArrayFromDb($pdo, $cartId)
{
    if (!$cartId) return [];
    
    $cart = [];
    $stmt = $pdo->prepare("SELECT product_id, quantity FROM sales_cart_product WHERE cart_id = ?");
    $stmt->execute([$cartId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        $cart[$row['product_id']] = (int)$row['quantity'];
    }
    return $cart;
}

/**
 * Merges the guest cart with the user's saved cart in the database upon login.
 */
function mergeCartOnLogin($pdo, $userId)
{
    $sessionId = session_id();
    
    // 1. Find/Create user cart
    $stmt = $pdo->prepare("SELECT cart_id FROM sales_cart WHERE user_id = ?");
    $stmt->execute([$userId]);
    $userCartId = $stmt->fetchColumn();

    // 2. Find guest cart
    $stmt = $pdo->prepare("SELECT cart_id FROM sales_cart WHERE session_id = ? AND user_id IS NULL");
    $stmt->execute([$sessionId]);
    $guestCartId = $stmt->fetchColumn();

    if (!$userCartId) {
        if ($guestCartId) {
            // Simply link guest cart to user
            $pdo->prepare("UPDATE sales_cart SET user_id = ? WHERE cart_id = ?")->execute([$userId, $guestCartId]);
            $_SESSION['cart_id'] = $guestCartId;
            return $guestCartId;
        } else {
            // Create fresh cart for user
            return getOrCreateCartId($pdo, $userId);
        }
    }

    if ($guestCartId && $guestCartId != $userCartId) {
        // Merge items from guest cart to user cart
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

        // Delete guest cart
        $pdo->prepare("DELETE FROM sales_cart WHERE cart_id = ?")->execute([$guestCartId]);
    }

    $_SESSION['cart_id'] = $userCartId;
    return $userCartId;
}

/**
 * Legacy support for full sync if needed (though we aim for item-level updates)
 */
function syncCartToDb($pdo, $userId, $cart)
{
    $cartId = getOrCreateCartId($pdo, $userId);
    if (!$cartId) return;

    try {
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM sales_cart_product WHERE cart_id = ?")->execute([$cartId]);
        if (!empty($cart)) {
            $stmtLine = $pdo->prepare("INSERT INTO sales_cart_product (cart_id, product_id, quantity) VALUES (?, ?, ?)");
            foreach ($cart as $pid => $qty) {
                if ($qty > 0) {
                    $stmtLine->execute([$cartId, (int)$pid, (int)$qty]);
                }
            }
        }
        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log("Cart sync error: " . $e->getMessage());
    }
}

/**
 * Gets cart metadata (shipping, coupon) from database.
 */
function getCartMetadata($pdo, $cartId)
{
    if (!$cartId) return ['shipping_method' => 'standard', 'coupon_code' => ''];
    
    $stmt = $pdo->prepare("SELECT shipping_method, coupon_code FROM sales_cart_metadata WHERE cart_id = ?");
    $stmt->execute([$cartId]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $data ?: ['shipping_method' => 'standard', 'coupon_code' => ''];
}

/**
 * Updates cart metadata in database.
 */
function updateCartMetadata($pdo, $cartId, $data)
{
    if (!$cartId) return false;
    
    $shipping = $data['shipping_method'] ?? 'standard';
    $coupon = $data['coupon_code'] ?? null;
    
    try {
        $check = $pdo->prepare("SELECT metadata_id FROM sales_cart_metadata WHERE cart_id = ?");
        $check->execute([$cartId]);
        if ($check->fetch()) {
            $stmt = $pdo->prepare("UPDATE sales_cart_metadata SET shipping_method = ?, coupon_code = ? WHERE cart_id = ?");
            $stmt->execute([$shipping, $coupon, $cartId]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO sales_cart_metadata (cart_id, shipping_method, coupon_code) VALUES (?, ?, ?)");
            $stmt->execute([$cartId, $shipping, $coupon]);
        }
        return true;
    } catch (Exception $e) {
        error_log("Cart metadata update error: " . $e->getMessage());
        return false;
    }
}

/**
 * Clears cart metadata (usually after order).
 */
function clearCartMetadata($pdo, $cartId)
{
    if (!$cartId) return;
    $pdo->prepare("DELETE FROM sales_cart_metadata WHERE cart_id = ?")->execute([$cartId]);
}
