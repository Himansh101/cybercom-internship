<?php
require_once __DIR__ . '/../partials/header.view.php';
?>

<form action="plp" method="GET" class="content-search-bar">
    <input type="text" name="search" placeholder="Search for products, brands and more..."
        value="<?php echo htmlspecialchars($searchQuery); ?>">
    <button type="submit"><i class="ri-search-line"></i></button>
</form>

<section class="hero">
    <div>
        <h1>Discover deals for everything you love</h1>
        <p>Curated gadgets, fashion essentials, and home comforts delivered fast.</p>
        <a class="btn btn-success btn-inline" href="plp">Shop Products</a>
    </div>
</section>

<section>
    <h2>Featured Products</h2>
    <div class="grid">
        <?php if (!empty($featuredProducts)): ?>
            <?php foreach ($featuredProducts as $id => $product):
                $categoryName = $categories[$product['cat_id']] ?? 'Uncategorized';
                ?>
                <div class="card product-card">
                    <div class="product-image-wrapper">
                        <?php if (isset($product['item_shipping_type'])): ?>
                            <div class="shipping-badge <?php echo $product['item_shipping_type']; ?>">
                                <?php echo ucfirst($product['item_shipping_type']); ?>
                            </div>
                        <?php endif; ?>
                        <img src="assets/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                    </div>
                    <div class="card-content">
                        <div class="category-badge"><?php echo $categoryName; ?></div>
                        <h3><?php echo $product['name']; ?></h3>
                        <div class="price">₹<?php echo number_format($product['price']); ?></div>
                        <a class="btn btn-primary btn-sm btn-inline mt-18" href="url_key=<?php echo $product['url_key']; ?>">
                            View Details
                        </a>
                    </div>
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
        <?php
        if (isset($categories)): ?>
            <?php foreach ($categories as $cat_id => $name): ?>
                <a class="pill indexPill" href="categories[]=<?php echo $cat_id; ?>"><?php echo $name; ?></a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<section>
    <h2>Popular Brands</h2>
    <div class="grid">
        <?php
        if (isset($brands)): ?>
            <?php foreach ($brands as $brand_id => $brand): ?>
                <a class="card brandCard" href="brands[]=<?php echo $brand_id; ?>">
                    <h3><?php echo $brand['name']; ?></h3>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php
require_once __DIR__ . '/../partials/footer.view.php';
?>