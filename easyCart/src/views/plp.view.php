<?php
// Note: This view handles both full page and AJAX grid/pagination updates
if (!(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
    require_once __DIR__ . '/../partials/header.view.php';
}

/** Helper functions for rendering encapsulated within the view for this task **/
function renderProductGrid($paginatedProducts, $categories = [], $brands = [])
{
    ob_start();
    echo '<div class="grid">';

    if (empty($paginatedProducts)) {
        echo '<div class="no-results" style="grid-column: 1/-1; text-align: center; padding: 60px;">
                <i class="ri-search-2-line" style="font-size: 3rem; color: #cbd5e1;"></i>
                <p>No products match your current filters.</p>
                <a href="plp" style="color: #6366f1;">Clear all filters</a>
              </div>';
    } else {
        foreach ($paginatedProducts as $id => $product) {
            $catName   = $categories[$product['cat_id']] ?? 'Uncategorized';
            $brandId   = $product['brand_id'] ?? null;
            $brandName = ($brandId && isset($brands[$brandId])) ? $brands[$brandId]['name'] : 'Generic';
            $isOut     = !$product['in_stock'];
?>
            <div class="card product-card <?php echo $isOut ? 'is-out-of-stock' : ''; ?>">
                <div class="product-image-wrapper">
                    <?php if ($isOut): ?>
                        <div class="stock-badge">Out of Stock</div>
                    <?php endif; ?>
                    <?php if (isset($product['item_shipping_type'])): ?>
                        <div class="shipping-badge <?php echo $product['item_shipping_type']; ?>">
                            <?php echo ucfirst($product['item_shipping_type']); ?>
                        </div>
                    <?php endif; ?>
                    <img src="assets/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>"
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
                        <a class="btn-view" href="<?php echo $product['url_key']; ?>">
                            View Details <i class="ri-arrow-right-line"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
    <?php
        }
    }
    echo '</div>';
    return ob_get_clean();
}

function renderPagination($pageNumber, $totalPages)
{
    if ($totalPages <= 1) return '';

    ob_start();
    $queryParams = $_GET;
    unset($queryParams['page']);
    $queryString = http_build_query($queryParams);
    if (!empty($queryString)) {
        $queryString .= '&';
    }
    ?>
    <div class="pagination">
        <?php if ($pageNumber > 1): ?>
            <a href="<?php echo $queryString . ($queryString ? '&' : ''); ?>page=<?php echo $pageNumber - 1; ?>" class="pagination-btn pagination-prev" data-page="<?php echo $pageNumber - 1; ?>">
                <i class="ri-arrow-left-line"></i> Previous
            </a>
        <?php endif; ?>

        <div class="pagination-numbers">
            <?php
            $maxVisible = 1;
            $startPage = max(1, $pageNumber - floor($maxVisible / 2));
            $endPage = $startPage + $maxVisible - 1;

            if ($endPage > $totalPages) {
                $endPage = $totalPages;
                $startPage = max(1, $endPage - $maxVisible + 1);
            }

            if ($startPage > 1): ?>
                <a href="<?php echo $queryString . ($queryString ? '&' : ''); ?>page=1" class="pagination-btn" data-page="1">1</a>
                <?php if ($startPage > 2): ?>
                    <span class="pagination-dots">...</span>
                <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                <a href="<?php echo $queryString . ($queryString ? '&' : ''); ?>page=<?php echo $i; ?>"
                    class="pagination-btn <?php echo ($i == $pageNumber) ? 'active' : ''; ?>" data-page="<?php echo $i; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($endPage < $totalPages): ?>
                <?php if ($endPage < $totalPages - 1): ?>
                    <span class="pagination-dots">...</span>
                <?php endif; ?>
                <a href="<?php echo $queryString . ($queryString ? '&' : ''); ?>page=<?php echo $totalPages; ?>" class="pagination-btn" data-page="<?php echo $totalPages; ?>"><?php echo $totalPages; ?></a>
            <?php endif; ?>
        </div>

        <?php if ($pageNumber < $totalPages): ?>
            <a href="<?php echo $queryString . ($queryString ? '&' : ''); ?>page=<?php echo $pageNumber + 1; ?>" class="pagination-btn pagination-next" data-page="<?php echo $pageNumber + 1; ?>">
                Next <i class="ri-arrow-right-line"></i>
            </a>
        <?php endif; ?>
    </div>
<?php
    return ob_get_clean();
}

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $response = [
        'status' => 'success',
        'grid_html' => renderProductGrid($paginatedProducts, $categories, $brands),
        'pagination_html' => renderPagination($pageNumber, $totalPages),
        'count_text' => $totalVisible > 0 ? "Showing {$startItem}-{$endItem} of {$totalVisible} Items" : "0 Items Found",
        'total_visible' => $totalVisible
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>

<form id="filter-form" action="" method="GET">
    <input type="hidden" name="page" value="<?php echo $pageNumber; ?>">
    <div class="shop-container">
        <aside class="sidebar-filters">
            <div class="filter-scroll-area">
                <div class="filter-group">
                    <h4><i class="ri-money-dollar-circle-line"></i> Price Range</h4>
                    <div class="price-inputs">
                        <div class="price-input-wrapper">
                            <span class="currency">₹</span>
                            <input type="number" name="min_price" class="js-filter-input" placeholder="Min"
                                value="<?php echo $minPrice > 0 ? $minPrice : ''; ?>">
                        </div>
                        <div class="price-separator">-</div>
                        <div class="price-input-wrapper">
                            <span class="currency">₹</span>
                            <input type="number" name="max_price" class="js-filter-input" placeholder="Max"
                                value="<?php echo $maxPrice < 1000000 ? $maxPrice : ''; ?>">
                        </div>
                    </div>
                </div>

                <div class="filter-group">
                    <h4><i class="ri-checkbox-circle-line"></i> Availability</h4>
                    <div class="filter-options-list">
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
                </div>

                <div class="filter-group">
                    <h4><i class="ri-grid-line"></i> Categories</h4>
                    <div class="filter-options-list">
                        <?php 
                        foreach ($categories as $id => $name): ?>
                            <label class="filter-option">
                                <input type="checkbox" name="categories[]" class="js-filter-input" value="<?php echo $id; ?>"
                                    <?php echo in_array((string)$id, $selectedCats) ? 'checked' : ''; ?>>
                                <?php echo $name; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="filter-group">
                    <h4><i class="ri-government-line"></i> Brands</h4>
                    <div class="filter-options-list">
                        <?php 
                        foreach ($brands as $id => $bData): ?>
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
                    <a href="plp" class="btn-reset">Reset All</a>
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
                    <span class="product-count" id="product-count">
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
                        <select name="sort" id="sort">
                            <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="price_low" <?php echo $sortBy === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo $sortBy === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="name_asc" <?php echo $sortBy === 'name_asc' ? 'selected' : ''; ?>>Name: A to Z</option>
                            <option value="name_desc" <?php echo $sortBy === 'name_desc' ? 'selected' : ''; ?>>Name: Z to A</option>
                        </select>
                    </div>
                </div>
            </div>

            <div id="product-grid-container" style="position: relative;">
                <div id="loading-overlay">
                    <i class="ri-loader-4-line ri-spin" style="font-size: 3rem; color: #6366f1;"></i>
                </div>
                <?php echo renderProductGrid($paginatedProducts, $categories, $brands); ?>
            </div>

            <div id="pagination-container">
                <?php echo renderPagination($pageNumber, $totalPages); ?>
            </div>
        </section>
    </div>
</form>

<?php
if (!(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
    require_once __DIR__ . '/../partials/footer.view.php';
}
?>
