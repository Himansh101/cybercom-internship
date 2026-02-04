<?php
require_once __DIR__ . '/../partials/header.view.php';
?>

<a href="cart" class="back-btn"><i class="ri-arrow-left-line"></i> Back to Cart</a>

<?php if (isset($_SESSION['checkout_errors'])): ?>
    <div class="error-summary" style="background: #fee2e2; border: 1px solid #ef4444; color: #b91c1c; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h4 style="margin: 0 0 10px 0; display: flex; align-items: center; gap: 8px;"><i class="ri-error-warning-fill"></i> Please fix the following errors:</h4>
        <ul style="margin: 0; padding-left: 20px;">
            <?php foreach (explode("\n", $_SESSION['checkout_errors']) as $err): ?>
                <li><?php echo htmlspecialchars($err); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php unset($_SESSION['checkout_errors']); ?>
<?php endif; ?>

<form action="/../controllers/checkout.handler" id="checkout-form" method="POST" data-subtotal="<?php echo $subtotal; ?>">
    <input type="hidden" name="action" value="place_order">
    <div class="checkout-layout">

        <section class="checkout-details">
            <h1>Checkout Details</h1>

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" placeholder="John Doe" minlength="3" pattern="[a-zA-Z\s]+" title="Name should only contain letters and spaces, and be at least 3 characters long." required>
                <span class="error-message" id="name-error">Please enter a valid name (min 3 letters).</span>
            </div>

            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="mobile">Mobile Number (with +91)</label>
                    <input type="tel" id="mobile" name="mobile" value="<?php echo htmlspecialchars($_POST['mobile'] ?? ''); ?>" pattern="(\+91)[6-9][0-9]{9}" title="Enter a valid Indian mobile number starting with +91 (e.g., +919876543210)" placeholder="+919876543210" required>
                    <span class="error-message" id="mobile-error">Enter valid +91 number.</span>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="john@example.com" required>
                    <span class="error-message" id="email-error">Enter a valid email.</span>
                </div>
            </div>

            <div class="form-group">
                <label for="address">Delivery Address</label>
                <textarea id="address" name="address" placeholder="House No, Street, Locality" minlength="10" title="Please provide a more detailed address (at least 10 characters)." required><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                <span class="error-message" id="address-error">Address must be at least 10 characters.</span>
            </div>

            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>" placeholder="Mumbai" required>
                    <span class="error-message" id="city-error">Please enter your city.</span>
                </div>
                <div class="form-group">
                    <label for="pincode">Pincode</label>
                    <input type="text" id="pincode" name="pincode" value="<?php echo htmlspecialchars($_POST['pincode'] ?? ''); ?>" pattern="[1-9][0-9]{5}" title="Enter a valid 6-digit Indian Pincode" placeholder="400001" required>
                    <span class="error-message" id="pincode-error">Enter valid 6-digit Pincode.</span>
                </div>
            </div>

            <div class="shipping-section mt-12">
                <div class="shipping-header">
                    <h3>Shipping Method</h3>
                    <?php if ($hasFreightItem): ?>
                        <p style="font-size: 0.85rem; color: #64748b; margin-top: 4px;">
                            <i class="ri-information-line"></i> Your cart contains freight items. Only premium shipping options are available.
                        </p>
                    <?php elseif ($subtotal > 300): ?>
                        <p style="font-size: 0.85rem; color: #64748b; margin-top: 4px;">
                            <i class="ri-information-line"></i> High-value cart (>₹300). Only premium shipping options are available.
                        </p>
                    <?php else: ?>
                        <p style="font-size: 0.85rem; color: #64748b; margin-top: 4px;">
                            <i class="ri-information-line"></i> Standard shipping options available for your cart.
                        </p>
                    <?php endif; ?>
                </div>

                <div class="shipping-options">
                    <?php 
                    $allMethods = get_all_shipping_methods($pdo, $subtotal);
                    foreach ($allMethods as $code => $m): 
                        $isAllowed = in_array($code, $allowedShippingMethods);
                    ?>
                        <label class="shipping-card <?php echo !$isAllowed ? 'disabled' : ''; ?>">
                            <input type="radio" name="shipping_method" value="<?php echo $code; ?>"
                                <?php echo ($method === $code) ? 'checked' : ''; ?>
                                <?php echo !$isAllowed ? 'disabled' : ''; ?>>
                            <div class="shipping-info">
                                <span class="method-title"><?php echo htmlspecialchars($m['name']); ?></span>
                                <span class="method-desc"><?php echo htmlspecialchars($m['description']); ?></span>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <aside class="checkout-summary">
            <h2>Order Summary</h2>
            <div class="summary-items">
                <?php foreach ($checkoutItems as $id => $item): ?>
                    <div class="summary-product-row">
                        <div class="summary-img-wrapper">
                            <img src="<?php echo $item['image']; ?>" alt="">
                            <?php if ($item['quantity'] > 1): ?><span class="qty-badge"><?php echo $item['quantity']; ?></span><?php endif; ?>
                        </div>
                        <div class="summary-product-info">
                            <span class="product-name"><?php echo htmlspecialchars($item['name']); ?></span>
                            <span class="product-price">₹<?php echo number_format($item['item_total']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="coupon-section">
                <div class="coupon-header">
                    <h3>Have a Coupon?</h3>
                </div>
                <div class="coupon-input-group">
                    <input type="text" id="coupon_code" name="coupon_code" value="<?php echo htmlspecialchars($coupon_code); ?>" placeholder="Enter coupon code (e.g., SAVE10, SAVE20)">
                    <?php if ($discount > 0): ?>
                        <button type="button" id="apply_coupon" class="btn btn-secondary" data-state="remove">Remove</button>
                    <?php else: ?>
                        <button type="button" id="apply_coupon" class="btn btn-secondary" data-state="apply">Apply</button>
                    <?php endif; ?>
                </div>
                <?php if (!empty($discount_message)): ?>
                    <div class="coupon-message" style="margin-top: 8px; font-size: 14px; color: <?php echo ($discount > 0) ? '#10b981' : '#ef4444'; ?>;">
                        <?php echo htmlspecialchars($discount_message); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="summary-totals">
                <div class="row"><span>Subtotal</span><span>₹<?php echo number_format($subtotal); ?></span></div>
                <?php if ($discount > 0): ?>
                    <div class="row discount-row"><span>Discount (<?php echo $discount_percentage; ?>%)</span><span>-₹<?php echo number_format($discount); ?></span></div>
                <?php endif; ?>
                <div class="row"><span>GST (18%)</span><span id="summary-tax">₹<?php echo number_format($gst); ?></span></div>
                <div class="row"><span>Shipping</span><span id="summary-shipping">₹<?php echo number_format($shipping); ?></span></div>
                <hr>
                <div class="row total">
                    <span>Total</span>
                    <span id="summary-total">₹<?php echo number_format($final_total); ?></span>
                </div>
            </div>
            <div class="payment-section mt-18">
                <h3>Payment Method</h3>
                <div class="payment-options">
                    <label class="payment-card">
                        <input type="radio" name="payment" value="cod" checked>
                        <div class="payment-info">
                            <i class="ri-truck-line"></i>
                            <div class="method-details">
                                <span class="method-title">Cash on Delivery</span>
                                <span class="method-desc">Pay when your package arrives</span>
                            </div>
                        </div>
                    </label>

                    <label class="payment-card">
                        <input type="radio" name="payment" value="stripe">
                        <div class="payment-info">
                            <i class="ri-bank-card-line"></i>
                            <div class="method-details">
                                <span class="method-title">Stripe (Credit/Debit Card)</span>
                                <span class="method-desc">Secure payment via Stripe</span>
                            </div>
                        </div>
                        <div class="stripe-badges">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/b/ba/Stripe_Logo%2C_revised_2016.svg" alt="Stripe" style="height: 15px;">
                        </div>
                    </label>
                </div>
            </div>
            <button class="btn btn-success" type="submit">Place Order</button>
        </aside>
    </div>
</form>

<?php
require_once __DIR__ . '/../partials/footer.view.php';
?>
