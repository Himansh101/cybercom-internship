<?php
include 'data.php';

// 1. Initialize search query to avoid "Undefined variable" error
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

// 2. Filter products to get only the featured ones
$featuredProducts = array_filter($products, function ($p) {
    return isset($p['is_featured']) && $p['is_featured'] === true;
});
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyCart | Home</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="./styles/styles.css">
    <link rel="stylesheet" href="./styles/plp.css">
</head>

<body class="page-site">
    <header>
        <div class="logo">EasyCart</div>
        <nav>
            <a href="index.php" class="active">Home</a>
            <a href="plp.php">Products</a>
            <a href="cart.php">Cart</a>
            <a href="orders.php">My Orders</a>
            <a href="login.php">Login</a>
        </nav>
        <button class="mobile-menu-btn" id="mobile-menu-btn">
            <i class="ri-menu-line"></i>
        </button>
    </header>

    <main>
        <form action="plp.php" method="GET" class="content-search-bar">
            <input type="text" name="search" placeholder="Search for products, brands and more..."
                value="<?php echo htmlspecialchars($searchQuery); ?>">
            <button type="submit"><i class="ri-search-line"></i></button>
        </form>

        <section class="hero">
            <div>
                <h1>Discover deals for everything you love</h1>
                <p>Curated gadgets, fashion essentials, and home comforts delivered fast.</p>
                <a class="btn btn-success btn-inline" href="plp.php">Shop Products</a>
            </div>
        </section>

        <section>
            <h2>Featured Products</h2>
            <div class="grid">
                <?php if (!empty($featuredProducts)): ?>
                    <?php foreach ($featuredProducts as $id => $product):
                        $categoryName = $categories[$product['cat_id']];
                    ?>
                        <div class="card">
                            <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                            <div class="category-badge"><?php echo $categoryName; ?></div>
                            <h3><?php echo $product['name']; ?></h3>
                            <div class="meta">₹<?php echo number_format($product['price']); ?> · New Arrival</div>
                            <a class="btn btn-primary btn-sm btn-inline mt-18" href="pdp.php?id=<?php echo $id; ?>">
                                View Details
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No featured products available at the moment.</p>
                <?php endif; ?>
            </div>
        </section>

        <section>
            <h2>Popular Categories</h2>
            <div class="pill-list">
                <?php if (isset($categories)): ?>
                    <?php foreach ($categories as $cat_id => $name): ?>
                        <a class="pill indexPill" href="plp.php?categories[]=<?php echo $cat_id; ?>"><?php echo $name; ?></a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section>
            <h2>Popular Brands</h2>
            <div class="grid">
                <?php if (isset($brands)): ?>
                    <?php foreach ($brands as $brand_id => $brand): ?>
                        <a class="card brandCard" href="plp.php?brands[]=<?php echo $brand_id; ?>">
                            <h3><?php echo $brand['name']; ?></h3>

                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
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