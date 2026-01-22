<?php
session_start();
include 'data.php';

// --- HANDLE QUANTITY UPDATES & REMOVAL ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['product_id'];

  // Handle Increment/Decrement
  if (isset($_POST['action'])) {
    if ($_POST['action'] === 'plus') {
      $_SESSION['cart'][$id] += 1;
    } elseif ($_POST['action'] === 'minus') {
      $_SESSION['cart'][$id] -= 1;
      // Remove item if quantity drops to 0
      if ($_SESSION['cart'][$id] < 1) {
        unset($_SESSION['cart'][$id]);
      }
    }
  }

  // Handle Remove Button
  if (isset($_POST['remove'])) {
    unset($_SESSION['cart'][$id]);
  }

  // Refresh page to show updated totals
  header("Location: cart.php");
  exit();
}

$subtotal = 0;
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>EasyCart | Cart</title>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
  <link rel="stylesheet" href="./styles/styles.css">
  <link rel="stylesheet" href="./styles/cart.css">
</head>

<body class="page-site cart">
  <header>
    <div class="logo">EasyCart</div>
    <nav>
      <a href="index.php">Home</a>
      <a href="plp.php">Products</a>
      <a href="cart.php" class="active">Cart</a>
      <a href="orders.php">My Orders</a>
      <a href="login.php">Login</a>
    </nav>
  </header>

  <main>
    <a href="plp.php" class="back-btn"><i class="ri-arrow-left-line"></i> Continue Shopping</a>
    <div class="cart-layout">
      <section>
        <h1 class="mb-12">Cart Items</h1>
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
            <?php if (!empty($_SESSION['cart'])): ?>
              <?php foreach ($_SESSION['cart'] as $id => $quantity):
                $product = $products[$id];
                $item_total = $product['price'] * $quantity;
                $subtotal += $item_total;
              ?>
                <tr>
                  <td class="cart-img-cell">
                    <div class="cart-img-wrapper">
                      <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                    </div>
                  </td>

                  <td><?php echo $product['name']; ?></td>
                  <td>₹<?php echo number_format($product['price']); ?></td>
                  <td class="qty-cell">
                    <form method="POST" class="qty-control">
                      <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                      <button type="submit" name="action" value="minus" class="btn-qty minus">-</button>
                      <input type="number" value="<?php echo $quantity; ?>" readonly>
                      <button type="submit" name="action" value="plus" class="btn-qty plus">+</button>
                    </form>
                  </td>
                  <td class="subtotal">₹<?php echo number_format($item_total); ?></td>
                  <td>
                    <form method="POST" style="display:inline;">
                      <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                      <button type="submit" name="remove" class="btn-remove"><i class="ri-delete-bin-line"></i></button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" style="text-align:center; padding: 40px;">Your cart is empty.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </section>

      <aside class="summary">
        <h2>Price Summary</h2>
        <div class="row"><span>Subtotal</span><span>₹<?php echo number_format($subtotal); ?></span></div>
        <div class="row"><span>Shipping</span><span>₹99</span></div>
        <div class="row total"><span>Total</span><span>₹<?php echo number_format($subtotal + 99); ?></span></div>
        <a href="checkout.php" class="btn btn-primary mt-18 w-full">Proceed to Checkout</a>
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