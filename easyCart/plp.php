<?php
include 'data.php';

// 1. Initialize variables
$selectedCats   = isset($_GET['categories']) ? $_GET['categories'] : [];
$selectedBrands = isset($_GET['brands']) ? $_GET['brands'] : [];
$selectedStock  = isset($_GET['stock_status']) ? $_GET['stock_status'] : []; // NEW
$searchQuery    = isset($_GET['search']) ? trim($_GET['search']) : '';

$minPrice = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (int)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (int)$_GET['max_price'] : 1000000;
$sortBy   = $_GET['sort'] ?? 'newest';

// 2. Apply Sorting logic early (before rendering)
if ($sortBy === 'price_low') {
  uasort($products, fn($a, $b) => $a['price'] <=> $b['price']);
} elseif ($sortBy === 'price_high') {
  uasort($products, fn($a, $b) => $b['price'] <=> $a['price']);
} elseif ($sortBy === 'name_asc') {
  uasort($products, fn($a, $b) => strcasecmp($a['name'], $b['name']));
} elseif ($sortBy === 'name_desc') {
  uasort($products, fn($a, $b) => strcasecmp($b['name'], $a['name']));
}
// Default 'newest' uses the original order in data.php (array order)

/**
 * Filtered Render Function
 */
function renderSidebarFilteredGrid($products, $brands, $categories, $selCats, $selBrands, $search, $minP, $maxP, $selStock)
{
  echo '<div class="grid">';
  $count = 0;

  foreach ($products as $id => $product) {
    // Filter Logic
    $catMatch    = (empty($selCats) || in_array($product['cat_id'], $selCats));
    $brandMatch  = (empty($selBrands) || in_array($product['brand_id'], $selBrands));
    $searchMatch = empty($search) || (stripos($product['name'], $search) !== false);
    $priceMatch  = ($product['price'] >= $minP && $product['price'] <= $maxP);

    // NEW: Stock Match Logic
    // Assumes your data.php has 'in_stock' => true/false
    $status      = ($product['in_stock']) ? 'instock' : 'outofstock';
    $stockMatch  = (empty($selStock) || in_array($status, $selStock));

    if ($catMatch && $brandMatch && $searchMatch && $priceMatch && $stockMatch) {
      $count++;
      $catName = $categories[$product['cat_id']];
      $brandName = $brands[$product['brand_id']]['name'];
      $isOut = !$product['in_stock'];
?>
      <div class="card product-card <?php echo $isOut ? 'is-out-of-stock' : ''; ?>">
        <div class="product-image-wrapper" style="position: relative;">
          <?php if ($isOut): ?>
            <div class="stock-badge">Out of Stock</div>
          <?php endif; ?>
          <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>"
            style="<?php echo $isOut ? 'filter: grayscale(1); opacity: 0.6;' : ''; ?>">
        </div>
        <div class="card-content">
          <div class="card-meta-row">
            <span class="category-badge"><?php echo $catName; ?></span>
            <span class="brand-label"><?php echo $brandName; ?></span>
          </div>
          <h3><?php echo $product['name']; ?></h3>
          <div class="price">₹<?php echo number_format($product['price']); ?></div>

          <?php if ($isOut): ?>
            <button class="btn-view" disabled style="background: #cbd5e1; cursor: not-allowed; border:none;">
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

  if ($count === 0) {
    echo '<div class="no-results" style="grid-column: 1/-1; text-align: center; padding: 60px;">
                <i class="ri-search-2-line" style="font-size: 3rem; color: #cbd5e1;"></i>
                <p>No products match your current filters.</p>
                <a href="plp.php" style="color: #6366f1;">Clear all filters</a>
              </div>';
  }
  echo '</div>';
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>EasyCart | Shop</title>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet">
  <link rel="stylesheet" href="./styles/styles.css">
  <link rel="stylesheet" href="./styles/plp.css">

</head>

<body class="page-site plp">
  <header>
    <div class="logo">EasyCart</div>
    <nav>
      <a href="index.php">Home</a>
      <a href="plp.php" class="active">Products</a>
      <a href="cart.php">Cart</a>
      <a href="orders.php">My Orders</a>
      <a href="login.php">Login</a>
    </nav>
  </header>

  <main>
    <form action="plp.php" method="GET">
      <div class="shop-container">

        <aside class="sidebar-filters">
          <div class="filter-scroll-area">
            <div class="filter-group">
              <h4>Price Range</h4>
              <div style="display: flex; gap: 8px; align-items: center;">
                <input type="number" name="min_price" placeholder="Min"
                  value="<?php echo $minPrice > 0 ? $minPrice : ''; ?>"
                  style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px;">
                <input type="number" name="max_price" placeholder="Max"
                  value="<?php echo $maxPrice < 1000000 ? $maxPrice : ''; ?>"
                  style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px;">
              </div>
            </div>

            <div class="filter-group">
              <h4>Availability</h4>
              <label class="filter-option">
                <input type="checkbox" name="stock_status[]" value="instock"
                  <?php echo in_array('instock', $selectedStock) ? 'checked' : ''; ?>>
                In Stock
              </label>
              <label class="filter-option">
                <input type="checkbox" name="stock_status[]" value="outofstock"
                  <?php echo in_array('outofstock', $selectedStock) ? 'checked' : ''; ?>>
                Out of Stock
              </label>
            </div>

            <div class="filter-group">
              <h4>Categories</h4>
              <?php foreach ($categories as $id => $name): ?>
                <label class="filter-option">
                  <input type="checkbox" name="categories[]" value="<?php echo $id; ?>"
                    <?php echo in_array($id, $selectedCats) ? 'checked' : ''; ?>>
                  <?php echo $name; ?>
                </label>
              <?php endforeach; ?>
            </div>

            <div class="filter-group">
              <h4>Brands</h4>
              <?php foreach ($brands as $id => $bData): ?>
                <label class="filter-option">
                  <input type="checkbox" name="brands[]" value="<?php echo $id; ?>"
                    <?php echo in_array($id, $selectedBrands) ? 'checked' : ''; ?>>
                  <?php echo $bData['name']; ?>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="filter-actions">
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
                $totalVisible = 0;
                foreach ($products as $p) {
                  $cMatch = (empty($selectedCats) || in_array($p['cat_id'], $selectedCats));
                  $bMatch = (empty($selectedBrands) || in_array($p['brand_id'], $selectedBrands));
                  $sMatch = empty($searchQuery) || (stripos($p['name'], $searchQuery) !== false);
                  $pMatch = ($p['price'] >= $minPrice && $p['price'] <= $maxPrice);

                  $st = ($p['in_stock']) ? 'instock' : 'outofstock';
                  $stMatch = (empty($selectedStock) || in_array($st, $selectedStock));

                  if ($cMatch && $bMatch && $sMatch && $pMatch && $stMatch) $totalVisible++;
                }
                echo $totalVisible . " Items Found";
                ?>
              </span>
            </div>

            <div class="sort-wrapper">
              <label for="sort" style="font-size: 0.9rem; font-weight: 600; color: var(--color-gray-600);">Sort By:</label>
              <select name="sort" id="sort" onchange="this.form.submit()" style="padding: 8px 12px; border: 1px solid var(--color-gray-200); border-radius: 6px; background: white; cursor: pointer; font-size: 0.9rem;">
                <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                <option value="price_low" <?php echo $sortBy === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                <option value="price_high" <?php echo $sortBy === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                <option value="name_asc" <?php echo $sortBy === 'name_asc' ? 'selected' : ''; ?>>Name: A to Z</option>
                <option value="name_desc" <?php echo $sortBy === 'name_desc' ? 'selected' : ''; ?>>Name: Z to A</option>
              </select>
            </div>
          </div>

          <?php renderSidebarFilteredGrid($products, $brands, $categories, $selectedCats, $selectedBrands, $searchQuery, $minPrice, $maxPrice, $selectedStock); ?>
        </section>

      </div>
    </form>
  </main>

  <footer>
    <div class="footer-container">
      <div class="footer-col">
        <div class="logo">EasyCart</div>
        <p>Your one-stop destination for curated gadgets, fashion, and home essentials.</p>
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
          <li><a href="login.php">Login / Register</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Contact Us</h4>
        <div class="contact-info">
          <p><i class="ri-map-pin-2-line"></i> Silicon Valley, Bangalore, India</p>
          <p><i class="ri-phone-line"></i> +91 98765 43210</p>
          <p><i class="ri-mail-line"></i> support@easycart.com</p>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© 2026 EasyCart. All rights reserved. | Internship Project</p>
    </div>
  </footer>
</body>

</html>