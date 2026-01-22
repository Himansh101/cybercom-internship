<?php
session_start();
include 'data.php';

// 1. Get the product ID from the URL
$productId = isset($_GET['id']) ? $_GET['id'] : null;

// 2. Check if product exists in our updated data.php
if ($productId === null || !isset($products[$productId])) {
  header("Location: plp.php");
  exit();
}

$product = $products[$productId];

// LOOKUP LOGIC: Get names using the IDs stored in the product array
$categoryName = $categories[$product['cat_id']];
$brandName    = $brands[$product['brand_id']]['name'];

// 3. Logic to handle "Add to Cart"
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
  $id = $_POST['product_id'];

  if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
  }

  if (isset($_SESSION['cart'][$id])) {
    $_SESSION['cart'][$id] += 1;
  } else {
    $_SESSION['cart'][$id] = 1;
  }

  header("Location: cart.php");
  exit();
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EasyCart | <?php echo $product['name']; ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
  <link rel="stylesheet" href="./styles/styles.css">
  <link rel="stylesheet" href="./styles/pdp.css">
</head>

<body class="page-site pdp">
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
    <a href="plp.php" class="back-btn"><i class="ri-arrow-left-line"></i> Back to Products</a>

    <div class="layout">
      <div class="gallery">
        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
      </div>

      <div>
        <h1><?php echo $product['name']; ?></h1>
        <div class="price">₹<?php echo number_format($product['price']); ?></div>

        <div class="meta-row" style="display: flex; gap: 12px; align-items: center; margin: 12px 0;">
          <span class="category-badge" style="margin: 0;"><?php echo $categoryName; ?></span>
          <span class="meta">Brand: <strong><?php echo $brandName; ?></strong> · Ships in 24h</span>
        </div>

        <p><?php echo $product['description']; ?></p>

        <ul class="list">
          <li>Premium Build Quality from <?php echo $brandName; ?></li>
          <li>Official Manufacturer Warranty</li>
          <li>Fast & Secure Delivery</li>
        </ul>

        <div class="mt-18">
          <form method="POST">
            <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
            <button type="submit" name="add_to_cart" class="btn btn-success btn-inline">
              <i class="ri-shopping-cart-line"></i> Add to Cart
            </button>
          </form>
        </div>
      </div>
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