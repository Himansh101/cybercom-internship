<?php
session_start();
include 'data.php';

// --- HANDLE QUANTITY UPDATES & REMOVAL ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['product_id'])) {
    $id = $_POST['product_id'];
    $maxStock = $products[$id]['stock_count'] ?? 0;

    if (isset($_POST['action'])) {
      if ($_POST['action'] === 'plus') {
        if ($_SESSION['cart'][$id] < $maxStock) {
          $_SESSION['cart'][$id] += 1;
        } else {
          // Set error message in session to persist after redirect
          $_SESSION['stock_error'] = "Cannot add more. Only $maxStock units available for " . $products[$id]['name'];
        }
      } elseif ($_POST['action'] === 'minus') {
        $_SESSION['cart'][$id] -= 1;
        if ($_SESSION['cart'][$id] < 1) {
          unset($_SESSION['cart'][$id]);
        }
      }
    }

    if (isset($_POST['remove'])) {
      unset($_SESSION['cart'][$id]);
    }
  }
  header("Location: cart.php");
  exit();
}

$subtotal = 0;
$shipping_fee = 90;
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EasyCart | Shopping Cart</title>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
  <link rel="stylesheet" href="./styles/styles.css">
  <link rel="stylesheet" href="./styles/cart.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="js/main.js" defer></script>
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
    <button class="mobile-menu-btn" id="mobile-menu-btn"><i class="ri-menu-line"></i></button>
  </header>

  <main>
    <a href="plp.php" class="back-btn"><i class="ri-arrow-left-line"></i> Continue Shopping</a>

    <?php if (isset($_SESSION['stock_error'])): ?>
      <div class="stock-alert" style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin: 20px 0; border: 1px solid #fecaca;">
        <i class="ri-error-warning-line"></i> <?php echo $_SESSION['stock_error'];
        unset($_SESSION['stock_error']); ?>
      </div>
    <?php endif; ?>

    <div class="cart-layout">
      <section>
        <h1 class="mb-12">Shopping Cart</h1>
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
              <?php if (!empty($_SESSION['cart'])): ?>
                <?php foreach ($_SESSION['cart'] as $id => $quantity):
                  if (!isset($products[$id])) continue;
                  $product = $products[$id];
                  $item_total = $product['price'] * $quantity;
                  $subtotal += $item_total;
                  $maxStock = $product['stock_count'] ?? 0;
                  $isMaxed = ($quantity >= $maxStock);
                ?>
                  <tr>
                    <td class="cart-img-cell" data-label="Image">
                      <div class="cart-img-wrapper">
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                      </div>
                    </td>
                    <td data-label="Product">
                      <span class="product-name-text"><?php echo htmlspecialchars($product['name']); ?></span>
                      <?php if ($isMaxed): ?>
                        <small style="display:block; color: #e11d48; font-size: 0.7rem; margin-top: 4px;">Max stock: <?php echo $maxStock; ?></small>
                      <?php endif; ?>
                    </td>
                    <td class="price-cell" data-label="Price">₹<?php echo number_format($product['price']); ?></td>
                    <td class="qty-cell" data-label="Quantity">
                      <form method="POST" class="qty-control">
                        <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                        <button type="submit" name="action" value="minus" class="btn-qty minus">-</button>
                        <input type="number" value="<?php echo $quantity; ?>" readonly>
                        <button type="submit" name="action" value="plus" class="btn-qty plus"
                          <?php echo $isMaxed ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>+</button>
                      </form>
                    </td>
                    <td class="subtotal" data-label="Subtotal">₹<?php echo number_format($item_total); ?></td>
                    <td class="action-cell">
                      <form method="POST" class="remove-form">
                        <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                        <button type="button"
                          class="btn-remove js-delete-confirm"
                          data-name="<?php echo htmlspecialchars($product['name']); ?>">
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
          <div class="row"><span>Subtotal</span><span>₹<?php echo number_format($subtotal); ?></span></div>
          <div class="row"><span>Shipping</span><span>₹<?php echo number_format($subtotal > 0 ? $shipping_fee : 0); ?></span></div>
          <hr class="summary-divider">
          <div class="row total"><span>Total Amount</span><span>₹<?php echo number_format($subtotal > 0 ? ($subtotal + $shipping_fee) : 0); ?></span></div>
        </div>
        <a href="<?php echo ($subtotal > 0) ? 'checkout.php' : '#'; ?>" class="btn <?php echo ($subtotal > 0) ? 'btn-primary' : 'btn-disabled'; ?> mt-18 w-full">Proceed to Checkout</a>
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