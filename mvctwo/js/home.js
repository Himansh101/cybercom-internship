document.addEventListener('DOMContentLoaded', function () {

    // ================================
    // Hero: Countdown Timer
    // ================================
    var countdownEl = document.getElementById('hero-countdown');
    if (countdownEl) {
        var endTime = new Date().getTime() + 24 * 60 * 60 * 1000;

        function updateCountdown() {
            var now = new Date().getTime();
            var diff = endTime - now;

            if (diff <= 0) {
                countdownEl.textContent = 'Sale Ended';
                return;
            }

            var h = Math.floor(diff / (1000 * 60 * 60));
            var m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            var s = Math.floor((diff % (1000 * 60)) / 1000);

            countdownEl.textContent =
                String(h).padStart(2, '0') + ':' +
                String(m).padStart(2, '0') + ':' +
                String(s).padStart(2, '0');
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);
    }

    // ================================
    // Featured: Add to Cart Toast
    // ================================
    var toast = document.getElementById('cart-toast');

    window.addToCart = function (productName) {
        if (!toast) return;
        toast.textContent = '✅ ' + productName + ' added to cart!';
        toast.style.display = 'block';

        setTimeout(function () {
            toast.style.display = 'none';
        }, 2500);
    };

    // ================================
    // Categories: Tile Highlight
    // ================================
    var tiles = document.querySelectorAll('.cat-tile');
    var label = document.getElementById('cat-label');

    tiles.forEach(function (tile) {
        tile.addEventListener('click', function () {
            tiles.forEach(function (t) {
                t.classList.remove('active');
            });

            tile.classList.add('active');

            if (label) {
                label.textContent = 'Browsing: ' + tile.getAttribute('data-category');
            }
        });
    });

});
