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

            <!-- Shipping Address Section -->
            <div class="address-section">
                <h3 style="margin-top: 0; margin-bottom: 15px; font-size: 1.1rem; color: #475569; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">Shipping Address</h3>
                
                <?php if ($userAddress): ?>
                    <div class="saved-address-options" style="margin-bottom: 20px; background: #f0f9ff; padding: 15px; border-radius: 8px; border: 1px solid #bae6fd;">
                        <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; margin-bottom: 10px;">
                            <input type="radio" name="address_selection" value="saved" checked onchange="toggleAddressSelection('saved')">
                            <div>
                                <span style="font-weight: 600; color: #0369a1; display: block;">Use Saved Address</span>
                                <span style="font-size: 0.9rem; color: #334155; display: block; margin-top: 4px;">
                                    <?php echo htmlspecialchars($userAddress['address'] . ', ' . $userAddress['city'] . ' - ' . $userAddress['pincode']); ?>
                                </span>
                            </div>
                        </label>
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="radio" name="address_selection" value="new" onchange="toggleAddressSelection('new')">
                            <span style="font-weight: 600; color: #334155;">Enter New Address</span>
                        </label>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="shipping_name">Full Name</label>
                    <input type="text" id="shipping_name" name="shipping_name" value="<?php echo htmlspecialchars($_POST['shipping_name'] ?? ''); ?>" placeholder="John Doe" minlength="3" pattern="[a-zA-Z\s]+" title="Name should only contain letters and spaces." required>
                    <span class="error-message" id="shipping_name-error">Please enter a valid name (min 3 letters).</span>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="shipping_mobile">Mobile Number</label>
                        <input type="tel" id="shipping_mobile" name="shipping_mobile" value="<?php echo htmlspecialchars($_POST['shipping_mobile'] ?? ''); ?>" pattern="(\+91)[6-9][0-9]{9}" title="Enter a valid +91 number" placeholder="+919876543210" required>
                        <span class="error-message" id="shipping_mobile-error">Enter valid +91 number.</span>
                    </div>

                    <div class="form-group">
                        <label for="shipping_email">Email Address</label>
                        <input type="email" id="shipping_email" name="shipping_email" value="<?php echo htmlspecialchars($_POST['shipping_email'] ?? ''); ?>" placeholder="john@example.com" required>
                        <span class="error-message" id="shipping_email-error">Enter a valid email.</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="shipping_address">Street Address</label>
                    <textarea id="shipping_address" name="shipping_address" placeholder="House No, Street, Locality" minlength="10" required><?php echo htmlspecialchars($_POST['shipping_address'] ?? ''); ?></textarea>
                    <span class="error-message" id="shipping_address-error">Address must be at least 10 characters.</span>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="shipping_city">City</label>
                        <input type="text" id="shipping_city" name="shipping_city" value="<?php echo htmlspecialchars($_POST['shipping_city'] ?? ''); ?>" placeholder="Mumbai" required>
                        <span class="error-message" id="shipping_city-error">Please enter your city.</span>
                    </div>
                    <div class="form-group">
                        <label for="shipping_pincode">Pincode</label>
                        <input type="text" id="shipping_pincode" name="shipping_pincode" value="<?php echo htmlspecialchars($_POST['shipping_pincode'] ?? ''); ?>" pattern="[1-9][0-9]{5}" placeholder="400001" required>
                        <span class="error-message" id="shipping_pincode-error">Enter valid 6-digit Pincode.</span>
                    </div>
                </div>
            </div>

            <!-- Billing Address Toggle -->
            <div class="form-group mt-6" style="background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <label class="checkbox-container" style="display: flex; align-items: center; gap: 10px; cursor: pointer; user-select: none;">
                    <input type="checkbox" id="billing_same_as_shipping" name="billing_same_as_shipping" value="1" checked style="width: auto; margin: 0;">
                    <span style="font-weight: 500; color: #334155;">My billing address is the same as my shipping address</span>
                </label>
            </div>

            <!-- Billing Address Section (Hidden by default) -->
            <div id="billing-address-section" class="address-section mt-6 hidden">
                <h3 style="margin-top: 0; margin-bottom: 15px; font-size: 1.1rem; color: #475569; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">Billing Address</h3>
                
                <div class="form-group">
                    <label for="billing_name">Full Name</label>
                    <input type="text" id="billing_name" name="billing_name" placeholder="John Doe">
                    <span class="error-message" id="billing_name-error">Please enter a valid name.</span>
                </div>

                <div class="form-group">
                    <label for="billing_address">Street Address</label>
                    <textarea id="billing_address" name="billing_address" placeholder="House No, Street, Locality"></textarea>
                    <span class="error-message" id="billing_address-error">Address must be at least 10 characters.</span>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="billing_city">City</label>
                        <input type="text" id="billing_city" name="billing_city" placeholder="Mumbai">
                        <span class="error-message" id="billing_city-error">Please enter your city.</span>
                    </div>
                    <div class="form-group">
                        <label for="billing_pincode">Pincode</label>
                        <input type="text" id="billing_pincode" name="billing_pincode" pattern="[1-9][0-9]{5}" placeholder="400001">
                        <span class="error-message" id="billing_pincode-error">Enter valid 6-digit Pincode.</span>
                    </div>
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
                        <input type="radio" name="payment_method" value="cod" <?php echo ($saved_payment_method === 'cod') ? 'checked' : ''; ?>>
                        <div class="payment-info">
                            <i class="ri-truck-line"></i>
                            <div class="method-details">
                                <span class="method-title">Cash on Delivery</span>
                                <span class="method-desc">Pay when your package arrives</span>
                            </div>
                        </div>
                    </label>

                    <label class="payment-card">
                        <input type="radio" name="payment_method" value="stripe" id="payment-stripe" <?php echo ($saved_payment_method === 'stripe') ? 'checked' : ''; ?>>
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

                    <!-- Stripe Element Container -->
                    <div id="stripe-card-container" class="mt-4 hidden" style="background: white; padding: 15px; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <div id="card-element"><!-- Stripe.js will inject the card Element here --></div>
                        <div id="card-errors" role="alert" style="color: #efa2a2; margin-top: 10px; font-size: 0.9em;"></div>
                    </div>
                </div>
            </div>
            <button class="btn btn-success" type="submit" id="submit-btn">Place Order</button>
        </aside>
    </div>
</form>

<script src="https://js.stripe.com/v3/"></script>
<script>
    const STRIPE_PUBLISHABLE_KEY = "<?php require_once __DIR__ . '/../config/stripe.php'; echo STRIPE_PUBLISHABLE_KEY; ?>";
    const USER_SAVED_ADDRESS = <?php echo json_encode($userAddress); ?>;
</script>

<?php
require_once __DIR__ . '/../partials/footer.view.php';
?>
