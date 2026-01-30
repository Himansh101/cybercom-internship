<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit();
}

include 'data/data.php';

include 'utils/coupon_utils.php';
include 'utils/shipping_utils.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user']);
$user = $_SESSION['user'] ?? null;

// Calculate total cart quantity (Distinct Items)
$cartQuantity = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
  $cartQuantity = count($_SESSION['cart']);
}

// Redirect to product page if cart is empty to prevent checking out nothing
if (empty($_SESSION['cart'])) {
  header("Location: plp.php");
  exit();
}

// 1. Calculate Subtotal first and determine allowed shipping methods
$subtotal = 0;
$hasFreightItem = false; // Track if cart has any freight items

foreach ($_SESSION['cart'] as $id => $quantity) {
  if (isset($products[$id])) {
    $subtotal += $products[$id]['price'] * $quantity;

    // Check if this product requires freight shipping
    if (isset($products[$id]['item_shipping_type']) && $products[$id]['item_shipping_type'] === 'freight') {
      $hasFreightItem = true;
    }
  }
}

// Determine allowed shipping methods based on cart contents and subtotal
// Priority 1: If ANY item has freight shipping type -> only freight and white_glove
// Priority 2: Else if subtotal > 300 -> only freight and white_glove
// Default: Otherwise -> only standard and express
if ($hasFreightItem) {
  // Cart contains at least one freight item
  $allowedShippingMethods = ['white_glove', 'freight'];
} elseif ($subtotal > 300) {
  // High-value cart (>300) without freight items
  $allowedShippingMethods = ['white_glove', 'freight'];
} else {
  // Standard cart
  $allowedShippingMethods = ['standard', 'express'];
}

// 2. Apply Coupon Discount (if valid)
$coupon_code = $_POST['coupon_code'] ?? $_SESSION['coupon_code'] ?? '';
$coupon_data = get_coupon_data($coupon_code, $subtotal);

$discount = $coupon_data['discount_amount'];
$discount_percentage = $coupon_data['discount_pct'];
$discount_message = $coupon_data['message'];

$discounted_subtotal = $subtotal - $discount;

// 3. Determine Shipping Cost based on button click or radio selection
$method = $_POST['shipping_method'] ?? $_SESSION['shipping_method'] ?? 'standard';

// Validate that selected method is allowed for current cart
if (!in_array($method, $allowedShippingMethods)) {
  $method = $allowedShippingMethods[0]; // Fallback to first allowed method
}

$_SESSION['shipping_method'] = $method; // Ensure it's persisted
$shipping = calculate_shipping_cost($method, $discounted_subtotal);

// 4. Calculate GST (18% on Discounted Subtotal)
$gst_rate = 0.18;
$gst = $discounted_subtotal * $gst_rate;

// 5. Calculate Final Total
$final_total = $discounted_subtotal + $shipping + $gst;
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EasyCart | Checkout</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
  <link rel="stylesheet" href="./styles/styles.css">
  <link rel="stylesheet" href="./styles/cart.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="js/auth.js" defer></script>
  <script src="js/checkout.js" defer></script>
</head>

<body class="page-site checkout">
  <header>
    <div class="logo">EasyCart</div>
    <nav>
      <a href="index.php">Home</a>
      <a href="plp.php">Products</a>
      <a href="cart.php" id="cart-nav-link">Cart<?php if ($cartQuantity > 0): ?><span class="cart-badge"><?php echo $cartQuantity; ?></span><?php endif; ?></a>
      <a href="orders.php">My Orders</a>
      <?php if ($isLoggedIn): ?>
        <span class="user-greeting" style="color: #6366f1; font-weight: 600; font-size: 0.9rem; border-left: 1px solid #e2e8f0; padding-left: 15px; margin-left: 5px;">
          Hi, <?php echo htmlspecialchars(explode(' ', $user['name'])[0]); ?>
        </span>
        <a href="logout.php">Logout</a>
      <?php else: ?>
        <a href="login.php">Login</a>
      <?php endif; ?>
    </nav>
    <button class="mobile-menu-btn" id="mobile-menu-btn">
      <i class="ri-menu-line"></i>
    </button>
  </header>

  <main>
    <a href="cart.php" class="back-btn"><i class="ri-arrow-left-line"></i> Back to Cart</a>

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

    <form action="checkout_handler.php" id="checkout-form" method="POST" data-subtotal="<?php echo $subtotal; ?>">
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
              <label class="shipping-card <?php echo !in_array('standard', $allowedShippingMethods) ? 'disabled' : ''; ?>">
                <input type="radio" name="shipping_method" value="standard"
                  <?php echo ($method === 'standard') ? 'checked' : ''; ?>
                  <?php echo !in_array('standard', $allowedShippingMethods) ? 'disabled' : ''; ?>>
                <div class="shipping-info">
                  <span class="method-title">Standard Shipping</span>
                  <span class="method-desc">3-5 Business Days</span>
                </div>
              </label>

              <label class="shipping-card <?php echo !in_array('express', $allowedShippingMethods) ? 'disabled' : ''; ?>">
                <input type="radio" name="shipping_method" value="express"
                  <?php echo ($method === 'express') ? 'checked' : ''; ?>
                  <?php echo !in_array('express', $allowedShippingMethods) ? 'disabled' : ''; ?>>
                <div class="shipping-info">
                  <span class="method-title">Express Shipping</span>
                  <span class="method-desc">1-2 Business Days</span>
                </div>
              </label>

              <label class="shipping-card <?php echo !in_array('white_glove', $allowedShippingMethods) ? 'disabled' : ''; ?>">
                <input type="radio" name="shipping_method" value="white_glove"
                  <?php echo ($method === 'white_glove') ? 'checked' : ''; ?>
                  <?php echo !in_array('white_glove', $allowedShippingMethods) ? 'disabled' : ''; ?>>
                <div class="shipping-info">
                  <span class="method-title">White Glove Delivery</span>
                  <span class="method-desc">Premium In-Home Setup</span>
                </div>
              </label>

              <label class="shipping-card <?php echo !in_array('freight', $allowedShippingMethods) ? 'disabled' : ''; ?>">
                <input type="radio" name="shipping_method" value="freight"
                  <?php echo ($method === 'freight') ? 'checked' : ''; ?>
                  <?php echo !in_array('freight', $allowedShippingMethods) ? 'disabled' : ''; ?>>
                <div class="shipping-info">
                  <span class="method-title">Freight Shipping</span>
                  <span class="method-desc">Heavy/Bulky Items</span>
                </div>
              </label>
            </div>
          </div>
        </section>

        <aside class="checkout-summary">
          <h2>Order Summary</h2>
          <div class="summary-items">
            <?php foreach ($_SESSION['cart'] as $id => $quantity):
              $product = $products[$id];
            ?>
              <div class="summary-product-row">
                <div class="summary-img-wrapper">
                  <img src="<?php echo $product['image']; ?>" alt="">
                  <?php if ($quantity > 1): ?><span class="qty-badge"><?php echo $quantity; ?></span><?php endif; ?>
                </div>
                <div class="summary-product-info">
                  <span class="product-name"><?php echo $product['name']; ?></span>
                  <span class="product-price">₹<?php echo number_format($product['price'] * $quantity); ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="coupon-section">
            <div class="coupon-header">
              <h3>Have a Coupon?</h3>
            </div>
            <div class="coupon-input-group">
              <input type="text" id="coupon_code" name="coupon_code" value="<?php echo htmlspecialchars($coupon_code); ?>" placeholder="Enter coupon code (e.g., SAVE5, SAVE10, SAVE15, SAVE20)">
              <button type="button" id="apply_coupon" class="btn btn-secondary">Apply</button>
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
  </main>
  <footer>
    <div class="footer-container">
      <div class="footer-col">
        <div class="logo">EasyCart</div>
        <p>Your one-stop destination for curated gadgets, fashion, and home essentials. Quality delivered to your doorstep.</p>
        <div class="social-links">
          <a href="#"><i class="ri-facebook-fill"></i></a>
          <a href="#"><i class="ri-instagram-line"></i></a>
          <a href="#"><i class="ri-twitter-x-line"></i></a>
        </div>
      </div>

      <div class="footer-col">
        <h4>Quick Links</h4>
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="plp.php">Shop Products</a></li>
          <li><a href="cart.php">My Cart</a></li>
          <li><a href="orders.php">Track Orders</a></li>
          <li><a href="login.php">Login / Register</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Contact Us</h4>
        <div class="contact-info">
          <p><i class="ri-map-pin-2-line"></i> 123 Tech Park, Silicon Valley, Bangalore, India</p>
          <p><i class="ri-phone-line"></i> +91 98765 43210</p>
          <p><i class="ri-mail-line"></i> support@easycart.com</p>
          <p><i class="ri-time-line"></i> Mon - Sat: 9:00 AM - 7:00 PM</p>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <p>© 2026 EasyCart. All rights reserved. | Internship Project</p>
    </div>
  </footer>
  <script src="js/main.js"></script>
</body>

</html>