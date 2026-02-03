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
                    <?php if ($isLoggedIn): ?>
                        <li><a href="plp.php">Shop Products</a></li>
                        <li><a href="cart.php">My Cart</a></li>
                        <li><a href="orders.php">Track Orders</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login / Register</a></li>
                    <?php endif; ?>
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
    <script src="assets/js/main.js"></script>
    <?php if (isset($extraScripts)): foreach ($extraScripts as $script): ?>
        <script src="assets/js/<?php echo $script; ?>"></script>
    <?php endforeach; endif; ?>
</body>

</html>
