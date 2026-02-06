<?php
require_once __DIR__ . '/../partials/header.view.php';
?>

<div class="admin-container">
    <h1 class="admin-title"><i class="ri-settings-4-line"></i> Admin Panel</h1>

    <!-- Stats Cards -->
    <div class="admin-stats">
        <div class="stat-card">
            <i class="ri-box-3-line"></i>
            <div class="stat-info">
                <span class="stat-value"><?php echo $totalProducts; ?></span>
                <span class="stat-label">Products</span>
            </div>
        </div>
        <div class="stat-card">
            <i class="ri-shopping-bag-line"></i>
            <div class="stat-info">
                <span class="stat-value"><?php echo $totalOrders; ?></span>
                <span class="stat-label">Orders</span>
            </div>
        </div>
        <div class="stat-card">
            <i class="ri-user-line"></i>
            <div class="stat-info">
                <span class="stat-value"><?php echo $totalUsers; ?></span>
                <span class="stat-label">Users</span>
            </div>
        </div>
    </div>

    <!-- Import/Export Section -->
    <div class="admin-grid">
        <!-- Import Card -->
        <div class="admin-card">
            <h2><i class="ri-upload-cloud-2-line"></i> Import Products</h2>
            <p>Upload a CSV file to add products in bulk. Existing SKUs will be skipped.</p>
            <form id="import-form" enctype="multipart/form-data">
                <input type="hidden" name="action" value="import_products">
                <div class="file-upload-area" id="drop-zone">
                    <i class="ri-file-upload-line"></i>
                    <p>Drag & Drop CSV file or <label for="csv-file" class="file-label">browse</label></p>
                    <input type="file" id="csv-file" name="csv_file" accept=".csv" hidden>
                    <span id="file-name" class="file-name"></span>
                </div>
                <button type="submit" class="btn btn-primary" id="import-btn" disabled>
                    <i class="ri-upload-2-line"></i> Import Products
                </button>
            </form>
            <div id="import-results" class="import-results hidden"></div>
        </div>

        <!-- Export Card -->
        <div class="admin-card">
            <h2><i class="ri-download-cloud-2-line"></i> Export Products</h2>
            <p>Download all products as a CSV file for backup or editing.</p>
            <a href="src/controllers/admin.handler?action=export_products" class="btn btn-secondary">
                <i class="ri-download-2-line"></i> Download CSV
            </a>
            <div class="export-info">
                <h4>CSV Format</h4>
                <code>sku, name, price, stock_count, category, brand_id, description, image_url, shipping_type, in_stock</code>
            </div>
        </div>
    </div>

    <!-- Sample CSV Template -->
    <div class="admin-card full-width">
        <h2><i class="ri-file-list-3-line"></i> Sample CSV Template</h2>
        <p>Use this format for importing products. <a href="src/controllers/admin.handler?action=download_template" class="link">Download Template</a></p>
        <pre class="csv-preview">sku,name,price,stock_count,category,brand_id,description,image_url,shipping_type,in_stock
SKU-001,Premium Headphones,2499.00,50,Electronics,br_01,High quality wireless headphones,images/headphones.jpg,standard,1
SKU-002,Office Desk,8999.00,10,Furniture,br_02,Modern ergonomic office desk,images/desk.jpg,freight,1</pre>
    </div>
</div>

<?php
require_once __DIR__ . '/../partials/footer.view.php';
?>
