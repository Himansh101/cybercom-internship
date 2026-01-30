<?php
session_start();

include 'data/data.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user']);
$user = $_SESSION['user'] ?? null;

// Calculate total cart quantity (Distinct Items)
$cartQuantity = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cartQuantity = count($_SESSION['cart']);
}

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
    <script src="./js/main.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/auth.js" defer></script>
</head>

<body class="page-site pdp">
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

                    <?php if (isset($product['item_shipping_type'])): ?>
                        <div class="shipping-type-info" style="margin-top: 12px;">
                            <span class="shipping-badge-pdp <?php echo $product['item_shipping_type']; ?>" style="display: inline-block; padding: 6px 14px; border-radius: 50px; font-size: 0.85rem; font-weight: 600;">
                                <i class="ri-truck-line"></i> <?php echo ucfirst($product['item_shipping_type']); ?> Shipping
                            </span>
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

                <div class="actions" id="cart-action-container" style="margin-top: 30px;">
                    <?php if (!$product['in_stock'] || $product['stock_count'] <= 0): ?>
                        <button class="btn btn-disabled" disabled style="opacity: 0.5; cursor: not-allowed; width: 100%;">
                            <i class="ri-error-warning-line"></i> Out of Stock
                        </button>
                    <?php elseif ($currentQtyInCart > 0): ?>
                        <!-- Quantity Controls if already in cart -->
                        <div class="pdp-qty-control" style="display: flex; align-items: center; gap: 15px; background: #f8fafc; padding: 10px 20px; border-radius: 12px; border: 1px solid #e2e8f0; width: fit-content;">
                            <button type="button" class="btn-qty minus js-pdp-qty-btn" data-action="minus" data-id="<?php echo $productId; ?>" style="width: 40px; height: 40px; border-radius: 50%; border: 1px solid #cbd5e1; background: white; font-size: 1.2rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                                <i class="ri-subtract-line"></i>
                            </button>
                            <span class="js-pdp-qty-value" style="font-size: 1.2rem; font-weight: 700; min-width: 30px; text-align: center; color: #1e293b;">
                                <?php echo $currentQtyInCart; ?>
                            </span>
                            <button type="button" class="btn-qty plus js-pdp-qty-btn" data-action="plus" data-id="<?php echo $productId; ?>"
                                style="width: 40px; height: 40px; border-radius: 50%; border: 1px solid #cbd5e1; background: white; font-size: 1.2rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; <?php echo $currentQtyInCart >= $product['stock_count'] ? 'opacity: 0.5; cursor: not-allowed;' : ''; ?>"
                                <?php echo $currentQtyInCart >= $product['stock_count'] ? 'disabled' : ''; ?>>
                                <i class="ri-add-line"></i>
                            </button>
                        </div>
                        <div class="pdp-stock-warning" style="margin-top: 8px; font-size: 0.8rem; color: #e11d48; font-weight: 500;">
                            <?php if ($currentQtyInCart >= $product['stock_count']): ?>
                                Max stock reached
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <!-- Standard Add to Cart Button -->
                        <form id="add-to-cart-form" method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                            <input type="hidden" name="action" value="add">
                            <button type="submit" id="add-to-cart-btn" class="btn btn-success" style="width: 100%; justify-content: center; display: flex; align-items: center; gap: 8px;">
                                <i class="ri-shopping-cart-line"></i> Add to Cart
                            </button>
                        </form>
                    <?php endif; ?>
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