<?php
session_start();
include 'data.php';

// --- HANDLE QUANTITY UPDATES & REMOVAL ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['product_id'])) {
    $id = $_POST['product_id'];

    // Handle Increment/Decrement
    if (isset($_POST['action'])) {
      if ($_POST['action'] === 'plus') {
        $_SESSION['cart'][$id] += 1;
      } elseif ($_POST['action'] === 'minus') {
        $_SESSION['cart'][$id] -= 1;
        if ($_SESSION['cart'][$id] < 1) {
          unset($_SESSION['cart'][$id]);
        }
      }
    }

    // Handle Remove Button
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
    <button class="mobile-menu-btn" id="mobile-menu-btn">
      <i class="ri-menu-line"></i>
    </button>
  </header>

  <main>
    <a href="plp.php" class="back-btn"><i class="ri-arrow-left-line"></i> Continue Shopping</a>

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
                  // Skip if product doesn't exist in data.php
                  if (!isset($products[$id])) continue;

                  $product = $products[$id];
                  $item_total = $product['price'] * $quantity;
                  $subtotal += $item_total;
                ?>
                  <tr>
                    <td class="cart-img-cell" data-label="Image">
                      <div class="cart-img-wrapper">
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                      </div>
                    </td>

                    <td data-label="Product">
                      <span class="product-name-text"><?php echo htmlspecialchars($product['name']); ?></span>
                    </td>

                    <td class="price-cell" data-label="Price">₹<?php echo number_format($product['price']); ?></td>

                    <td class="qty-cell" data-label="Quantity">
                      <form method="POST" class="qty-control">
                        <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                        <button type="submit" name="action" value="minus" class="btn-qty minus">-</button>
                        <input type="number" value="<?php echo $quantity; ?>" readonly>
                        <button type="submit" name="action" value="plus" class="btn-qty plus">+</button>
                      </form>
                    </td>

                    <td class="subtotal" data-label="Subtotal">₹<?php echo number_format($item_total); ?></td>

                    <td class="action-cell" data-label="Action">
                      <form method="POST" style="display:inline;">
                        <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                        <button type="submit" name="remove" class="btn-remove" title="Remove Item">
                          <i class="ri-delete-bin-line"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="empty-msg">
                    <i class="ri-shopping-cart-2-line" style="font-size: 3rem; color: #cbd5e1; display: block; margin-bottom: 10px;"></i>
                    Your cart is empty.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>

      <aside class="summary">
        <h2>Price Summary</h2>
        <div class="summary-details">
          <div class="row">
            <span>Subtotal</span>
            <span>₹<?php echo number_format($subtotal); ?></span>
          </div>
          <div class="row">
            <span>Shipping</span>
            <span>₹<?php echo number_format($subtotal > 0 ? $shipping_fee : 0); ?></span>
          </div>
          <hr class="summary-divider">
          <div class="row total">
            <span>Total Amount</span>
            <span>₹<?php echo number_format($subtotal > 0 ? ($subtotal + $shipping_fee) : 0); ?></span>
          </div>
        </div>

        <?php if ($subtotal > 0): ?>
          <a href="checkout.php" class="btn btn-primary mt-18 w-full">Proceed to Checkout</a>
        <?php else: ?>
          <button class="btn btn-disabled mt-18 w-full" disabled>Proceed to Checkout</button>
        <?php endif; ?>
      </aside>
    </div>
  </main>

  <script src="js/main.js"></script>
</body>

</html>