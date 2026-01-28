document.addEventListener('DOMContentLoaded', () => {
    // --- 1. Mobile Menu Toggle Logic ---
    const menuBtn = document.getElementById('mobile-menu-btn');
    const nav = document.querySelector('nav');

    if (menuBtn && nav) {
        const icon = menuBtn.querySelector('i');

        const toggleMenu = (isOpen) => {
            nav.classList.toggle('active', isOpen);
            if (isOpen) {
                icon.classList.replace('ri-menu-line', 'ri-close-line');
                document.body.style.overflow = 'hidden';
            } else {
                icon.classList.replace('ri-close-line', 'ri-menu-line');
                document.body.style.overflow = '';
            }
        };

        menuBtn.addEventListener('click', () => {
            const isOpening = !nav.classList.contains('active');
            toggleMenu(isOpening);
        });

        // Close menu when clicking a link (useful for one-page sections)
        nav.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => toggleMenu(false));
        });
    }

        const successData = document.getElementById('order-success-data');

        if (successData) {
            const message = successData.dataset.message;

            Swal.fire({
                title: 'Success!',
                text: message,
                icon: 'success',
                confirmButtonColor: '#10b981'
            });
        }
    });

    /**
     * Update the cart badge globally across pages
     * @param {number} count - Total items in cart
     */
    function updateCartBadge(count) {
        // 1. Target by ID (most reliable)
        let cartLinks = [];
        const idLink = document.getElementById('cart-nav-link');
        if (idLink) cartLinks.push(idLink);

        // 2. Fallback: Find all links to cart.php (footer, etc.)
        const allLinks = document.querySelectorAll('a[href*="cart.php"]');
        allLinks.forEach(link => {
            if (!cartLinks.includes(link)) cartLinks.push(link);
        });

        cartLinks.forEach(link => {
            let badge = link.querySelector('.cart-badge');

            if (count > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'cart-badge';
                    link.appendChild(badge);
                }

                // If count changed, add a little "bump" animation
                if (badge.textContent !== String(count)) {
                    badge.textContent = count;
                    badge.style.animation = 'none';
                    badge.offsetHeight; // trigger reflow
                    badge.style.animation = 'badgeUpdateBump 0.4s ease-out';
                }
            } else if (badge) {
                badge.remove();
            }
        });
    }

    // Add the bump animation to the document if not already present
    if (!document.getElementById('cart-badge-anim')) {
        const style = document.createElement('style');
        style.id = 'cart-badge-anim';
        style.innerHTML = `
        @keyframes badgeUpdateBump {
            0% { transform: scale(1); }
            50% { transform: scale(1.4); }
            100% { transform: scale(1); }
        }
    `;
        document.head.appendChild(style);
    }