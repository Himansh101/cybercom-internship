<?php
session_start();
include 'data.php';

// Redirect to product page if cart is empty to prevent checking out nothing
if (empty($_SESSION['cart'])) {
  header("Location: plp.php");
  exit();
}

// 1. Calculate Subtotal first
$subtotal = 0;
foreach ($_SESSION['cart'] as $id => $quantity) {
  if (isset($products[$id])) {
    $subtotal += $products[$id]['price'] * $quantity;
  }
}

// 2. Determine Shipping Cost based on button click or radio selection
$shipping = 90; // Default
$method = $_POST['shipping_method'] ?? 'standard';

if ($method === 'fast') {
  $shipping = 150;
}
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
</head>

<body class="page-site checkout">
  <header>
    <div class="logo">EasyCart</div>
    <nav>
      <a href="index.php">Home</a>
      <a href="plp.php">Products</a>
      <a href="cart.php">Cart</a>
      <a href="orders.php">My Orders</a>
      <a href="login.php">Login</a>
    </nav>
  </header>

  <main>
    <a href="cart.php" class="back-btn"><i class="ri-arrow-left-line"></i> Back to Cart</a>

    <form action="orders.php" id="checkout-form" method="POST">
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
            </div>

            <div class="shipping-options">
              <label class="shipping-card">
                <input type="radio" name="shipping_method" value="standard" <?php echo ($method === 'standard') ? 'checked' : ''; ?>>
                <div class="shipping-info">
                  <span class="method-title">Standard Delivery</span>
                  <span class="method-desc">3-5 Business Days</span>
                </div>
                <span class="method-price">₹90</span>
              </label>

              <label class="shipping-card">
                <input type="radio" name="shipping_method" value="fast" <?php echo ($method === 'fast') ? 'checked' : ''; ?>>
                <div class="shipping-info">
                  <span class="method-title">Fast Delivery</span>
                  <span class="method-desc">1-2 Business Days</span>
                </div>
                <span class="method-price">₹150</span>
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

          <div class="summary-totals">
            <div class="row"><span>Subtotal</span><span>₹<?php echo number_format($subtotal); ?></span></div>
            <div class="row"><span>Shipping</span><span id="summary-shipping">₹<?php echo number_format($shipping); ?></span></div>
            <hr>
            <div class="row total">
              <span>Total</span>
              <span id="summary-total">₹<?php echo number_format($subtotal + $shipping); ?></span>
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
  <script>
    const form = document.getElementById('checkout-form');
    const inputs = form.querySelectorAll('input:not([type="radio"]), textarea');

    inputs.forEach(input => {
      input.addEventListener('blur', () => {
        validateField(input);
      });
      input.addEventListener('input', () => {
        const errorSpan = document.getElementById(input.id + '-error');
        if (errorSpan && errorSpan.style.display === 'block') {
          validateField(input);
        }
      });
    });

    function validateField(input) {
      const errorSpan = document.getElementById(input.id + '-error');
      if (!errorSpan) return;

      if (!input.checkValidity()) {
        errorSpan.style.display = 'block';
        input.style.borderColor = '#ef4444';
      } else {
        errorSpan.style.display = 'none';
        input.style.borderColor = '';
      }
    }

    const subtotal = <?php echo $subtotal; ?>;
    const shippingRadios = document.querySelectorAll('input[name="shipping_method"]');
    const shippingDisplay = document.getElementById('summary-shipping');
    const totalDisplay = document.getElementById('summary-total');

    function updateSummary() {
      const selectedMethod = document.querySelector('input[name="shipping_method"]:checked').value;
      const shippingCost = selectedMethod === 'fast' ? 150 : 90;
      const total = subtotal + shippingCost;

      // Update the summary display with formatted numbers
      shippingDisplay.textContent = `₹${shippingCost.toLocaleString()}`;
      totalDisplay.textContent = `₹${total.toLocaleString()}`;
    }

    shippingRadios.forEach(radio => {
      radio.addEventListener('change', updateSummary);
    });

    form.addEventListener('submit', (e) => {
      // Remove the btn-refresh check since the button is gone

      let isValid = true;
      inputs.forEach(input => {
        validateField(input);
        if (!input.checkValidity()) {
          isValid = false;
        }
      });

      if (!isValid) {
        e.preventDefault();
        const firstError = form.querySelector('.error-message[style*="display: block"]');
        if (firstError) {
          firstError.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
          });
        }
      }
    });
  </script>
</body>

</html>