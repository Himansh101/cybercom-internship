<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit();
}

include 'data.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user']);
$user = $_SESSION['user'] ?? null;

// 1. Calculate total cart quantity for header
$cartQuantity = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
  foreach ($_SESSION['cart'] as $quantity) {
    $cartQuantity += $quantity;
  }
}

// 2. SUCCESS MESSAGE: Check for session message from checkout
$orderSuccessMessage = $_SESSION['order_success'] ?? null;
if ($orderSuccessMessage) {
  unset($_SESSION['order_success']); // Clear so it only shows once
}

?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EasyCart | My Orders</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
  <link rel="stylesheet" href="./styles/styles.css">
  <link rel="stylesheet" href="./styles/orders.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="js/auth.js" defer></script>

  <?php if ($orderSuccessMessage): ?>
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        Swal.fire({
          title: 'Success!',
          text: <?php echo json_encode($orderSuccessMessage); ?>,
          icon: 'success',
          confirmButtonColor: '#10b981'
        });
      });
    </script>
  <?php endif; ?>
</head>

<body class="page-site orders">
  <header>
    <div class="logo">EasyCart</div>
    <nav>
      <a href="index.php">Home</a>
      <a href="plp.php">Products</a>
      <a href="cart.php" id="cart-nav-link">Cart<?php if ($cartQuantity > 0): ?><span class="cart-badge"><?php echo $cartQuantity; ?></span><?php endif; ?></a>
      <a href="orders.php" class="active">My Orders</a>
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
    <div class="container" style="max-width: 800px; margin: 0 auto; padding: 40px 20px;">
      <h1 class="mb-12">My Orders</h1>
      <div class="order-list">

        <?php if (!empty($orders)): ?>
          <?php foreach ($orders as $order): ?>
            <div class="order-card">
              <div class="order-header">
                <div class="order-info">
                  <h3 style="margin:0 0 5px 0;">Order #<?php echo $order['order_id']; ?></h3>
                  <div class="order-meta" style="color: #64748b; font-size: 0.9rem;">
                    <i class="ri-calendar-line"></i> <?php echo $order['date']; ?>
                    <span style="margin: 0 10px;">•</span>
                    <strong style="color: #1e293b;">₹<?php echo number_format($order['total']); ?></strong>
                  </div>
                </div>
                <div class="status-badge <?php echo $order['status']; ?>">
                  <?php echo ucfirst($order['status']); ?>
                </div>
              </div>

              <div class="order-items">
                <p style="font-size: 0.85rem; font-weight: 600; color: #94a3b8; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.05em;">
                  Purchased Items
                </p>
                <?php
                foreach ($order['items'] as $pID):
                  if (isset($products[$pID])):
                    $productName = $products[$pID]['name'];
                ?>
                    <span class="order-item-chip">
                      <i class="ri-shopping-bag-3-line"></i> <?php echo $productName; ?>
                    </span>
                <?php
                  endif;
                endforeach;
                ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div style="text-align: center; padding: 60px 20px;">
            <i class="ri-history-line" style="font-size: 4rem; color: #cbd5e1;"></i>
            <p>No order history found.</p>
            <a href="plp.php" class="btn btn-primary btn-inline mt-18">Start Shopping</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <footer>
    <div class="footer-container">
      <div class="footer-col">
        <div class="logo">EasyCart</div>
        <p>Quality gadgets and essentials delivered to your doorstep.</p>
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
          <li><a href="orders.php">My Orders</a></li>
          <li><a href="login.php">Login</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Contact Us</h4>
        <div class="contact-info">
          <p><i class="ri-mail-line"></i> support@easycart.com</p>
          <p><i class="ri-phone-line"></i> +91 98765 43210</p>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© 2026 EasyCart. Internship Project</p>
    </div>
  </footer>
  <script src="js/main.js"></script>
</body>

</html>