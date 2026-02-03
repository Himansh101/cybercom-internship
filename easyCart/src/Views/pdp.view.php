<?php
require_once __DIR__ . '/../partials/header.view.php';
?>

<a href="plp" class="back-btn"><i class="ri-arrow-left-line"></i> Back to Products</a>

<div class="layout">
    <div class="gallery">
        <div class="main-img-container">
            <img id="main-product-image" src="assets/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>

        <?php if (isset($product['images']) && count($product['images']) > 1): ?>
            <div class="thumbnails">
                <?php foreach ($product['images'] as $idx => $imgSrc): ?>
                    <div class="thumb-item <?php echo $idx === 0 ? 'active' : ''; ?>" onclick="switchImage('assets/<?php echo $imgSrc; ?>', this)">
                        <img src="assets/<?php echo $imgSrc; ?>" alt="Thumbnail">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="product-details">
        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
        <div class="price">₹<?php echo number_format($product['price']); ?></div>

        <div class="pdp-meta-row">
            <span class="category-badge"><?php echo $categoryName; ?></span>
            <span class="meta">Brand: <strong><?php echo $brandName; ?></strong></span>
        </div>

        <div class="pdp-stock-container">
            <?php if ($product['in_stock'] && $product['stock_count'] > 0): ?>
                <div class="stock-info">
                    <span class="pdp-stock-badge in-stock">
                        <i class="ri-checkbox-circle-fill"></i> In Stock
                    </span>
                    <p class="pdp-stock-info-text">
                        <strong><?php echo $product['stock_count']; ?></strong> units available in warehouse
                    </p>
                </div>
            <?php else: ?>
                <div class="stock-info">
                    <span class="pdp-stock-badge out-of-stock">
                        Out of Stock
                    </span>
                    <p class="pdp-stock-info-text">Currently unavailable.</p>
                </div>
            <?php endif; ?>

            <?php if (isset($product['item_shipping_type'])): ?>
                <div class="pdp-shipping-info">
                    <span class="pdp-shipping-badge shipping-badge-pdp <?php echo $product['item_shipping_type']; ?>">
                        <i class="ri-truck-line"></i> <?php echo ucfirst($product['item_shipping_type']); ?> Shipping
                    </span>
                </div>
            <?php endif; ?>

            <?php if ($currentQtyInCart > 0): ?>
                <div class="pdp-cart-status">
                    <i class="ri-shopping-cart-fill"></i> You have <strong><?php echo $currentQtyInCart; ?></strong> in your cart.
                </div>
            <?php endif; ?>
        </div>

        <p class="description"><?php echo $product['description']; ?></p>

        <ul class="pdp-features-list">
            <li>Premium Quality from <?php echo $brandName; ?></li>
            <li>Official Manufacturer Warranty</li>
            <li>Fast & Secure Delivery</li>
        </ul>

        <div class="pdp-actions-container" id="cart-action-container">
            <?php if (!$product['in_stock'] || $product['stock_count'] <= 0): ?>
                <button class="btn btn-disabled pdp-add-to-cart-btn" disabled>
                    <i class="ri-error-warning-line"></i> Out of Stock
                </button>
            <?php elseif ($currentQtyInCart > 0): ?>
                <!-- Quantity Controls if already in cart -->
                <div class="pdp-qty-control">
                    <button type="button" class="btn-qty minus js-pdp-qty-btn pdp-qty-btn" data-action="minus" data-id="<?php echo $productId; ?>">
                        <i class="ri-subtract-line"></i>
                    </button>
                    <span class="js-pdp-qty-value pdp-qty-value">
                        <?php echo $currentQtyInCart; ?>
                    </span>
                    <button type="button" class="btn-qty plus js-pdp-qty-btn pdp-qty-btn" data-action="plus" data-id="<?php echo $productId; ?>"
                        <?php echo $currentQtyInCart >= $product['stock_count'] ? 'disabled' : ''; ?>>
                        <i class="ri-add-line"></i>
                    </button>
                </div>
                <div class="pdp-stock-warning">
                    <?php if ($currentQtyInCart >= $product['stock_count']): ?>
                        Max stock reached
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Standard Add to Cart Button -->
                <form id="add-to-cart-form" action="cart.handler.php" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                    <input type="hidden" name="action" value="add">
                    <button type="submit" id="add-to-cart-btn" class="btn btn-success pdp-add-to-cart-btn">
                        <i class="ri-shopping-cart-line"></i> Add to Cart
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../partials/footer.view.php';
?>
