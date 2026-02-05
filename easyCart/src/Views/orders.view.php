<?php
require_once __DIR__ . '/../partials/header.view.php';
?>

<div class="orders-container">
    <div class="orders-header">
        <h1>My Orders</h1>
        <p>Manage and track your recent orders</p>
    </div>

    <?php if (isset($_SESSION['order_success'])): ?>
        <div class="success-banner">
            <div class="success-icon"><i class="ri-checkbox-circle-fill"></i></div>
            <div class="success-content">
                <h3>Order Placed Successfully!</h3>
                <p><?php echo $_SESSION['order_success'];
                    unset($_SESSION['order_success']); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <div class="orders-list">
        <?php if (empty($userOrders)): ?>
            <div class="empty-orders">
                <div class="empty-icon"><i class="ri-shopping-bag-3-line"></i></div>
                <h2>No Orders Yet!</h2>
                <p>Looks like you haven't placed any orders yet. Start shopping to find something you love!</p>
                <a href="plp" class="btn btn-primary mt-18">Start Shopping</a>
            </div>
        <?php else: ?>
            <?php foreach ($userOrders as $order): ?>
                <div class="order-card">
                    <div class="order-card-header">
                        <div class="order-meta-grid">
                            <div class="meta-item">
                                <span class="label">Order ID</span>
                                <span class="value">#<?php echo $order['order_id']; ?></span>
                            </div>
                            <div class="meta-item">
                                <span class="label">Date</span>
                                <span class="value"><?php echo date('d M, Y', strtotime($order['date'])); ?></span>
                            </div>
                            <div class="meta-item">
                                <span class="label">Total Amount</span>
                                <span class="value">₹<?php echo number_format($order['total']); ?></span>
                            </div>
                            <div class="meta-item">
                                <span class="label">Status</span>
                                <span class="status-badge <?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="order-items-grid">
                        <?php foreach ($order['items'] as $item): ?>
                            <div class="order-item-chip">
                                <div class="item-img">
                                    <img src="assets/<?php echo $item['image']; ?>" alt="">
                                </div>
                                <div class="item-info">
                                    <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                    <span class="item-qty">Qty: <?php echo $item['qty']; ?></span>
                                </div>
                                <div class="item-price">
                                    ₹<?php echo number_format($item['price']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="order-card-footer">
                        <div class="shipping-info">
                            <i class="ri-truck-line"></i>
                            <span>Shipping via: <strong><?php echo ucfirst($order['shipping_method']); ?></strong></span>
                        </div>
                        <div class="order-actions">
                            <button class="btn-details" data-order-id="<?php echo $order['order_id']; ?>">View Details</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Order Detail Modal -->
<div id="orderModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Order Details <span id="modalOrderNo"></span></h2>
            <button class="close-modal"><i class="ri-close-line"></i></button>
        </div>
        <div class="modal-body">
            <div id="modalLoading" class="modal-loading">
                <div class="spinner"></div>
                <p>Fetching order details...</p>
            </div>
            <div id="modalContent" style="display: none;">
                <div class="modal-grid">
                    <div class="modal-left">
                        <h3>Items in Order</h3>
                        <div id="modalItems" class="modal-items-list">
                            <!-- Items injected here -->
                        </div>
                    </div>
                    <div class="modal-right">
                        <h3>Order Summary</h3>
                        <div class="price-breakup">
                            <div class="price-row"><span>Subtotal</span> <span id="detailSubtotal"></span></div>
                            <div class="price-row"><span>Shipping</span> <span id="detailShipping"></span></div>
                            <div class="price-row"><span>GST (18%)</span> <span id="detailTax"></span></div>
                            <div class="price-row total"><span>Total Amount</span> <span id="detailTotal"></span></div>
                        </div>
                        <div class="shipping-address-box">
                            <h3>Shipping Address</h3>
                            <p id="detailAddress"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../partials/footer.view.php';
?>
