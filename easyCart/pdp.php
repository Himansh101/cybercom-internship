<?php
session_start();
include 'data.php';

// 1. Get the product ID from the URL
$productId = $_GET['id'] ?? null;

// 2. Validate Product Existence
if ($productId === null || !isset($products[$productId])) {
    header("Location: plp.php");
    exit();
}

$product = $products[$productId];

// 3. Check current quantity in cart
$currentQtyInCart = $_SESSION['cart'][$productId] ?? 0;

// 4. Handle "Add to Cart"
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $id = $_POST['product_id'];
    $availableStock = $products[$id]['stock_count'] ?? 0;

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Check if user is trying to add more than available stock
    $newQty = ($SESSION['cart'][$id] ?? 0) + 1;
    
    if ($newQty <= $availableStock) {
        $_SESSION['cart'][$id] = $newQty;
        header("Location: cart.php");
        exit();
    } else {
        $error = "Sorry, no more stock available!";
    }
}

// Lookup Logic
$categoryName = $categories[$product['cat_id']] ?? 'Uncategorized';
$brandName    = $brands[$product['brand_id']]['name'] ?? 'Generic';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyCart | <?php echo htmlspecialchars($product['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="./styles/styles.css">
    <link rel="stylesheet" href="./styles/pdp.css">
    <script src="js/pdp.js" defer></script>
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
        <button class="mobile-menu-btn" id="mobile-menu-btn"><i class="ri-menu-line"></i></button>
    </header>

    <main>
        <a href="plp.php" class="back-btn"><i class="ri-arrow-left-line"></i> Back to Products</a>

        <div class="layout">
            <div class="gallery">
                <div class="main-img-container">
                    <img id="main-product-image" src="<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>

                <?php if (isset($product['images']) && count($product['images']) > 1): ?>
                    <div class="thumbnails">
                        <?php foreach ($product['images'] as $idx => $imgSrc): ?>
                            <div class="thumb-item <?php echo $idx === 0 ? 'active' : ''; ?>" onclick="switchImage('<?php echo $imgSrc; ?>', this)">
                                <img src="<?php echo $imgSrc; ?>" alt="Thumbnail">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="product-details">
                <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                <div class="price">₹<?php echo number_format($product['price']); ?></div>

                <div class="meta-row" style="display: flex; gap: 12px; align-items: center; margin: 12px 0;">
                    <span class="category-badge" style="margin: 0;"><?php echo $categoryName; ?></span>
                    <span class="meta">Brand: <strong><?php echo $brandName; ?></strong></span>
                </div>

                <div class="stock-container" style="border-top: 1px solid #eee; border-bottom: 1px solid #eee; padding: 15px 0; margin: 20px 0;">
                    <?php if ($product['in_stock'] && $product['stock_count'] > 0): ?>
                        <div class="stock-info">
                            <span style="background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 50px; font-size: 0.8rem; font-weight: 600;">
                                <i class="ri-checkbox-circle-fill"></i> In Stock
                            </span>
                            <p style="margin-top: 8px; color: #475569; font-size: 0.95rem;">
                                <strong><?php echo $product['stock_count']; ?></strong> units available in warehouse
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="stock-info">
                            <span style="background: #fee2e2; color: #991b1b; padding: 4px 12px; border-radius: 50px; font-size: 0.8rem; font-weight: 600;">
                                Out of Stock
                            </span>
                            <p style="margin-top: 8px; color: #94a3b8; font-size: 0.95rem;">Currently unavailable.</p>
                        </div>
                    <?php endif; ?>

                    <?php if ($currentQtyInCart > 0): ?>
                        <div class="cart-status-badge" style="background: #f0fdf4; color: #16a34a; padding: 8px 12px; border-radius: 6px; margin-top: 10px; display: inline-block; border: 1px solid #bbf7d0; font-size: 0.9rem;">
                            <i class="ri-shopping-cart-fill"></i> You have <strong><?php echo $currentQtyInCart; ?></strong> in your cart.
                        </div>
                    <?php endif; ?>
                </div>

                <p class="description"><?php echo $product['description']; ?></p>

                <ul class="features-list" style="margin: 20px 0; padding-left: 20px; color: #64748b; line-height: 1.6;">
                    <li>Premium Quality from <?php echo $brandName; ?></li>
                    <li>Official Manufacturer Warranty</li>
                    <li>Fast & Secure Delivery</li>
                </ul>

                <div class="actions" style="margin-top: 30px;">
                    <?php if (isset($error)): ?>
                        <p style="color: #e11d48; margin-bottom: 10px; font-weight: 600;"><?php echo $error; ?></p>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                        <button type="submit" name="add_to_cart" class="btn btn-success" 
                                <?php echo (!$product['in_stock'] || $product['stock_count'] <= 0) ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
                            <i class="ri-shopping-cart-line"></i> 
                            <?php echo ($product['in_stock'] && $product['stock_count'] > 0) ? 'Add to Cart' : 'Out of Stock'; ?>
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