<?php
require_once __DIR__ . '/../partials/header.view.php';
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</h1>
        <p>Your shopping activity at a glance</p>
    </div>

    <!-- Summary Metrics -->
    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-icon"><i class="ri-shopping-basket-2-line"></i></div>
            <div class="metric-info">
                <span class="label">Total Orders</span>
                <span id="stat-orders" class="value">---</span>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon"><i class="ri-money-dollar-circle-line"></i></div>
            <div class="metric-info">
                <span class="label">Total Spent</span>
                <span id="stat-spent" class="value">₹---</span>
            </div>
        </div>
        <!-- <div class="metric-card">
            <div class="metric-icon"><i class="ri-vip-crown-line"></i></div>
            <div class="metric-info">
                <span class="label">Membership</span>
                <span class="value">Premium</span>
            </div>
        </div> -->
    </div>

    <!-- Visualization Section -->
    <div class="dashboard-section chart-section">
        <div class="section-header">
            <h3><i class="ri-bar-chart-line"></i> Order Spending Trends</h3>
            <p>Your order frequency and amount over time</p>
        </div>
        <div class="chart-container">
            <div id="chartLoading" class="modal-loading">
                <div class="spinner"></div>
                <p>Loading your trends...</p>
            </div>
            <canvas id="orderChart" style="display: none;"></canvas>
            <div id="emptyChart" style="display: none;" class="empty-chart">
                <i class="ri-line-chart-line"></i>
                <p>No order data available for visualization yet.</p>
            </div>
        </div>
    </div>

    <div class="dashboard-footer-actions">
        <h3>What would you like to do next?</h3>
        <div class="actions-grid">
            <a href="plp" class="action-card">
                <i class="ri-search-eye-line"></i>
                <span>Explore Products</span>
            </a>
            <a href="orders" class="action-card">
                <i class="ri-history-line"></i>
                <span>Track My Orders</span>
            </a>
            <a href="cart" class="action-card">
                <i class="ri-shopping-cart-2-line"></i>
                <span>View My Cart</span>
            </a>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../partials/footer.view.php';
?>