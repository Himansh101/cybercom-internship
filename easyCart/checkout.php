<?php
session_start();
include 'data.php';

// Redirect to product page if cart is empty to prevent checking out nothing
if (empty($_SESSION['cart'])) {
  header("Location: plp.php");
  exit();
}

$subtotal = 0;
$shipping = 99;
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
    <div class="checkout-layout">
      <section>
        <h1>Checkout Details</h1>
        <form action="orders.php" id="checkout-form" method="POST">
          <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" placeholder="John Doe" required>
          </div>

          <div class="form-group">
            <label for="mobile">Mobile Number</label>
            <input
              type="tel"
              id="mobile"
              name="mobile"
              pattern="\+91[1-9][0-9]{9}"
              placeholder="+911234567890"
              title="Please enter a valid number starting with +91 followed by 10 digits"
              required>
          </div>

          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="john@example.com" required>
          </div>

          <div class="form-group">
            <label for="address">Delivery Address</label>
            <textarea id="address" name="address" placeholder="House No, Street, City, Pincode" required></textarea>
          </div>

          <div class="form-group">
            <label>Payment Method</label>
            <div class="radio-group">
              <label class="radio-option">
                <input type="radio" name="payment" value="card" checked>
                <span>Credit / Debit Card</span>
              </label>
              <label class="radio-option">
                <input type="radio" name="payment" value="upi">
                <span>UPI (Google Pay, PhonePe)</span>
              </label>
              <label class="radio-option">
                <input type="radio" name="payment" value="cod">
                <span>Cash on Delivery</span>
              </label>
            </div>
          </div>
        </form>
      </section>

      <aside>
        <h2>Order Summary</h2>
        <div class="summary-items">
          <?php
          foreach ($_SESSION['cart'] as $id => $quantity):
            $product = $products[$id];
            $item_total = $product['price'] * $quantity;
            $subtotal += $item_total;
          ?>
            <div class="row">
              <span><?php echo $product['name']; ?> <?php echo ($quantity > 1) ? "x$quantity" : ""; ?></span>
              <span>₹<?php echo number_format($item_total); ?></span>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="row"><span>Shipping (Express)</span><span>₹<?php echo number_format($shipping); ?></span></div>
        <hr style="border: 0; border-top: 1px solid #eee; margin: 12px 0;">
        <div class="row total">
          <span>Total</span>
          <span>₹<?php echo number_format($subtotal + $shipping); ?></span>
        </div>
        <button class="btn btn-success" type="submit" form="checkout-form">Place Order</button>
      </aside>
    </div>
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
</body>

</html>