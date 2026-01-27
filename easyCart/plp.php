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

// Calculate total cart quantity
$cartQuantity = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $quantity) {
        $cartQuantity += $quantity;
    }
}

/** 1. Initialize variables from GET parameters */
$selectedCats   = $_GET['categories'] ?? [];
$selectedBrands = $_GET['brands'] ?? [];
$selectedStock  = $_GET['stock_status'] ?? [];
$searchQuery    = isset($_GET['search']) ? trim($_GET['search']) : '';

$minPrice = (isset($_GET['min_price']) && $_GET['min_price'] !== '') ? (int)$_GET['min_price'] : 0;
$maxPrice = (isset($_GET['max_price']) && $_GET['max_price'] !== '') ? (int)$_GET['max_price'] : 1000000;
$sortBy   = $_GET['sort'] ?? 'newest';
$currentPage = (isset($_GET['page']) && $_GET['page'] > 0) ? (int)$_GET['page'] : 1;
$productsPerPage = 9;

/* 2. Sorting & Filtering Logic */
$filteredProducts = [];
if (isset($products) && is_array($products)) {
    // 2a. Filtering
    foreach ($products as $id => $product) {
        $catMatch    = (empty($selectedCats) || in_array($product['cat_id'], $selectedCats));
        $brandMatch  = (empty($selectedBrands) || in_array($product['brand_id'], $selectedBrands));
        $searchMatch = empty($searchQuery) || (stripos($product['name'], $searchQuery) !== false);
        $priceMatch  = ($product['price'] >= $minPrice && $product['price'] <= $maxPrice);
        $status      = ($product['in_stock']) ? 'instock' : 'outofstock';
        $stockMatch  = (empty($selectedStock) || in_array($status, $selectedStock));

        if ($catMatch && $brandMatch && $searchMatch && $priceMatch && $stockMatch) {
            $filteredProducts[$id] = $product;
        }
    }

    // 2b. Sorting
    uasort($filteredProducts, function ($a, $b) use ($sortBy, $filteredProducts) {
        // Step 1: Priority - Stock Status
        if ($a['in_stock'] !== $b['in_stock']) {
            return $b['in_stock'] <=> $a['in_stock']; // In stock first
        }

        // Step 2: Tie-breaker - User Selection
        switch ($sortBy) {
            case 'price_low':
                return $a['price'] <=> $b['price'];
            case 'price_high':
                return $b['price'] <=> $a['price'];
            case 'name_asc':
                return strcasecmp($a['name'], $b['name']);
            case 'name_desc':
                return strcasecmp($b['name'], $a['name']);
            case 'newest':
            default:
                // Efficient 'newest' sort using array keys (descending if they were added in order)
                // For this demo, let's assume higher ID means newer, so reverse ID comparison
                $idA = array_search($a, $filteredProducts);
                $idB = array_search($b, $filteredProducts);
                return $idB <=> $idA;
        }
    });
}

// 2c. Pagination Calculation
$totalVisible = count($filteredProducts);
$totalPages = ceil($totalVisible / $productsPerPage);
$startItem = $totalVisible > 0 ? ($currentPage - 1) * $productsPerPage + 1 : 0;
$endItem = min($currentPage * $productsPerPage, $totalVisible);
$offset = ($currentPage - 1) * $productsPerPage;
$paginatedProducts = array_slice($filteredProducts, $offset, $productsPerPage, true);

/* 3. Render Helper Function */
function renderProductGrid($paginatedProducts, $brands, $categories)
{
    echo '<div class="grid">';

    if (empty($paginatedProducts)) {
        echo '<div class="no-results" style="grid-column: 1/-1; text-align: center; padding: 60px;">
                <i class="ri-search-2-line" style="font-size: 3rem; color: #cbd5e1;"></i>
                <p>No products match your current filters.</p>
                <a href="plp.php" style="color: #6366f1;">Clear all filters</a>
              </div>';
    } else {
        foreach ($paginatedProducts as $id => $product) {
            $catName   = $categories[$product['cat_id']] ?? 'Uncategorized';
            $brandName = $brands[$product['brand_id']]['name'] ?? 'Generic';
            $isOut     = !$product['in_stock'];
?>
            <div class="card product-card <?php echo $isOut ? 'is-out-of-stock' : ''; ?>">
                <div class="product-image-wrapper">
                    <?php if ($isOut): ?>
                        <div class="stock-badge">Out of Stock</div>
                    <?php endif; ?>
                    <img src="<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>"
                        style="<?php echo $isOut ? 'filter: grayscale(1); opacity: 0.6;' : ''; ?>">
                </div>
                <div class="card-content">
                    <div class="card-meta-row">
                        <span class="category-badge"><?php echo $catName; ?></span>
                        <span class="brand-label"><?php echo $brandName; ?></span>
                    </div>
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <div class="price">₹<?php echo number_format($product['price']); ?></div>

                    <?php if ($isOut): ?>
                        <button class="btn-view" disabled style="background: #cbd5e1; cursor: not-allowed; border:none; width:100%; padding:10px; border-radius:6px;">
                            Unavailable
                        </button>
                    <?php else: ?>
                        <a class="btn-view" href="pdp.php?id=<?php echo $id; ?>">
                            View Details <i class="ri-arrow-right-line"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
<?php
        }
    }
    echo '</div>';
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyCart | Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="./styles/styles.css">
    <link rel="stylesheet" href="./styles/plp.css">
    <script src="./js/plp.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/auth.js" defer></script>
</head>

<body class="page-site plp">
    <header>
        <div class="logo">EasyCart</div>
        <nav>
            <a href="index.php">Home</a>
            <a href="plp.php" class="active">Products</a>
            <a href="cart.php">Cart<?php if ($cartQuantity > 0): ?><span class="cart-badge"><?php echo $cartQuantity; ?></span><?php endif; ?></a>
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
        <button class="mobile-menu-btn" id="mobile-menu-btn">
            <i class="ri-menu-line"></i>
        </button>
    </header>

    <main>
        <form id="filter-form" action="plp.php" method="GET">
            <input type="hidden" name="page" value="<?php echo $currentPage; ?>">
            <div class="shop-container">
                <aside class="sidebar-filters">
                    <div class="filter-scroll-area">
                        <div class="filter-group">
                            <h4>Price Range</h4>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <input type="number" name="min_price" class="js-filter-input" placeholder="Min"
                                    value="<?php echo $minPrice > 0 ? $minPrice : ''; ?>"
                                    style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px;">
                                <input type="number" name="max_price" class="js-filter-input" placeholder="Max"
                                    value="<?php echo $maxPrice < 1000000 ? $maxPrice : ''; ?>"
                                    style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px;">
                            </div>
                        </div>

                        <div class="filter-group">
                            <h4>Availability</h4>
                            <label class="filter-option">
                                <input type="checkbox" name="stock_status[]" class="js-filter-input" value="instock"
                                    <?php echo in_array('instock', $selectedStock) ? 'checked' : ''; ?>>
                                In Stock
                            </label>
                            <label class="filter-option">
                                <input type="checkbox" name="stock_status[]" class="js-filter-input" value="outofstock"
                                    <?php echo in_array('outofstock', $selectedStock) ? 'checked' : ''; ?>>
                                Out of Stock
                            </label>
                        </div>

                        <div class="filter-group">
                            <h4>Categories</h4>
                            <?php foreach ($categories as $id => $name): ?>
                                <label class="filter-option">
                                    <input type="checkbox" name="categories[]" class="js-filter-input" value="<?php echo $id; ?>"
                                        <?php echo in_array((string)$id, $selectedCats) ? 'checked' : ''; ?>>
                                    <?php echo $name; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <div class="filter-group">
                            <h4>Brands</h4>
                            <?php foreach ($brands as $id => $bData): ?>
                                <label class="filter-option">
                                    <input type="checkbox" name="brands[]" class="js-filter-input" value="<?php echo $id; ?>"
                                        <?php echo in_array((string)$id, $selectedBrands) ? 'checked' : ''; ?>>
                                    <?php echo $bData['name']; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="filter-actions" style="padding: 16px; border-top: 1px solid #e2e8f0;">
                        <button type="submit" class="btn-apply">Apply Filters</button>
                        <a href="plp.php" class="btn-reset">Reset All</a>
                    </div>
                </aside>

                <section>
                    <div class="content-search-bar">
                        <input type="text" name="search" placeholder="Search for products, brands and more..."
                            value="<?php echo htmlspecialchars($searchQuery); ?>">
                        <button type="submit"><i class="ri-search-line"></i></button>
                    </div>

                    <div class="shop-content-header">
                        <div>
                            <h1>Our Collection</h1>
                            <span class="product-count">
                                <?php
                                if ($totalVisible > 0) {
                                    echo "Showing {$startItem}-{$endItem} of {$totalVisible} Items";
                                } else {
                                    echo "0 Items Found";
                                }
                                ?>
                            </span>
                        </div>

                        <div class="sort-wrapper">
                            <label for="sort" style="font-size: 0.9rem; font-weight: 600; color: #64748b;">Sort By:</label>
                            <div class="sort-select-container">
                                <select name="sort" id="sort" onchange="this.form.submit()">
                                    <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                    <option value="price_low" <?php echo $sortBy === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                    <option value="price_high" <?php echo $sortBy === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                    <option value="name_asc" <?php echo $sortBy === 'name_asc' ? 'selected' : ''; ?>>Name: A to Z</option>
                                    <option value="name_desc" <?php echo $sortBy === 'name_desc' ? 'selected' : ''; ?>>Name: Z to A</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <?php renderProductGrid($paginatedProducts, $brands, $categories); ?>

                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php
                            $queryParams = $_GET;
                            unset($queryParams['page']);
                            $queryString = http_build_query($queryParams);
                            if (!empty($queryString)) {
                                $queryString .= '&';
                            }

                            if ($currentPage > 1): ?>
                                <a href="?<?php echo $queryString; ?>page=<?php echo $currentPage - 1; ?>" class="pagination-btn pagination-prev">
                                    <i class="ri-arrow-left-line"></i> Previous
                                </a>
                            <?php endif; ?>

                            <div class="pagination-numbers">
                                <?php
                                $startPage = max(1, $currentPage - 2);
                                $endPage = min($totalPages, $currentPage + 2);

                                if ($startPage > 1): ?>
                                    <a href="?<?php echo $queryString; ?>page=1" class="pagination-btn">1</a>
                                    <?php if ($startPage > 2): ?>
                                        <span class="pagination-dots">...</span>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <a href="?<?php echo $queryString; ?>page=<?php echo $i; ?>"
                                        class="pagination-btn <?php echo $i === $currentPage ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if ($endPage < $totalPages): ?>
                                    <?php if ($endPage < $totalPages - 1): ?>
                                        <span class="pagination-dots">...</span>
                                    <?php endif; ?>
                                    <a href="?<?php echo $queryString; ?>page=<?php echo $totalPages; ?>" class="pagination-btn"><?php echo $totalPages; ?></a>
                                <?php endif; ?>
                            </div>

                            <?php if ($currentPage < $totalPages): ?>
                                <a href="?<?php echo $queryString; ?>page=<?php echo $currentPage + 1; ?>" class="pagination-btn pagination-next">
                                    Next <i class="ri-arrow-right-line"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </section>
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
</body>

</html>