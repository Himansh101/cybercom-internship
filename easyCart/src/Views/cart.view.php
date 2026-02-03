<?php
require_once __DIR__ . '/../partials/header.view.php';
?>

<a href="plp.php" class="back-btn"><i class="ri-arrow-left-line"></i> Continue Shopping</a>

<?php if (isset($_SESSION['stock_error'])): ?>
    <div class="stock-alert" style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin: 20px 0; border: 1px solid #fecaca;">
        <i class="ri-error-warning-line"></i> <?php echo $_SESSION['stock_error'];
                                                unset($_SESSION['stock_error']); ?>
    </div>
<?php endif; ?>

<div class="cart-layout">
    <section>
        <h1 class="mb-12">Shopping Cart <span style="font-size: 1rem; color: #64748b; font-weight: normal; margin-left: 10px;">(<?php echo count($_SESSION['cart'] ?? []); ?> items)</span></h1>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    global $products;
                    if (!empty($_SESSION['cart'])): ?>
                        <?php foreach ($_SESSION['cart'] as $id => $quantity):
                            if (!isset($products[$id])) continue;
                            $product = $products[$id];
                            $item_total = $product['price'] * $quantity;
                            $maxStock = $product['stock_count'] ?? 0;
                            $isMaxed = ($quantity >= $maxStock);
                        ?>
                            <tr data-id="<?php echo $id; ?>">
                                <td class="cart-img-cell" data-label="Image">
                                    <div class="cart-img-wrapper">
                                        <img src="assets/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    </div>
                                </td>
                                <td data-label="Product">
                                    <span class="product-name-text"><?php echo htmlspecialchars($product['name']); ?></span>
                                    <div class="stock-warning">
                                        <?php if ($isMaxed): ?>
                                            <small style="display:block; color: #e11d48; font-size: 0.7rem; margin-top: 4px;">Max stock: <?php echo $maxStock; ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="price-cell" data-label="Price">₹<?php echo number_format($product['price']); ?></td>
                                <td class="qty-cell" data-label="Quantity">
                                    <div class="qty-control">
                                        <button type="button" class="btn-qty minus js-qty-btn" data-action="minus" data-id="<?php echo $id; ?>">-</button>
                                        <input type="number" class="js-qty-input" value="<?php echo $quantity; ?>" readonly>
                                        <button type="button" class="btn-qty plus js-qty-btn" data-action="plus" data-id="<?php echo $id; ?>"
                                            <?php echo $isMaxed ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>+</button>
                                    </div>
                                </td>
                                <td class="subtotal js-item-subtotal" data-label="Subtotal">₹<?php echo number_format($item_total); ?></td>
                                <td class="action-cell">
                                    <form class="remove-form">
                                        <input type="hidden" name="product_id" value="<?php echo $id; ?>">

                                        <button type="button"
                                            class="btn-remove js-delete-confirm"
                                            data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                            data-id="<?php echo $id; ?>">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="empty-msg"><i class="ri-shopping-cart-2-line" style="font-size: 3rem; color: #cbd5e1; display: block; margin-bottom: 10px;"></i>Your cart is empty.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <aside class="summary">
        <h2>Price Summary</h2>
        <div class="summary-details">
            <div class="row"><span>Subtotal</span><span id="cart-subtotal">₹<?php echo number_format($subtotal); ?></span></div>
            <div class="row">
                <span>Shipping</span>
                <span id="cart-shipping" data-method="<?php echo $defaultShippingMethod; ?>">
                    <?php
                    $methodNames = [
                        'standard' => 'Standard',
                        'express' => 'Express',
                        'freight' => 'Freight',
                        'white_glove' => 'White Glove'
                    ];
                    echo $methodNames[$defaultShippingMethod] . ' - ₹' . number_format($subtotal > 0 ? $shipping_fee : 0);
                    ?>
                </span>
            </div>
            <hr class="summary-divider">
            <div class="row total"><span>Total Amount</span><span id="cart-total">₹<?php echo number_format($subtotal > 0 ? ($subtotal + $shipping_fee) : 0); ?></span></div>
        </div>
        <a id="checkout-link" href="<?php echo ($subtotal > 0) ? ($isLoggedIn ? 'checkout.php' : 'login.php') : '#'; ?>" class="btn <?php echo ($subtotal > 0) ? 'btn-primary' : 'btn-disabled'; ?> mt-18 w-full">Proceed to Checkout</a>
    </aside>
</div>

<?php
require_once __DIR__ . '/../partials/footer.view.php';
?>
