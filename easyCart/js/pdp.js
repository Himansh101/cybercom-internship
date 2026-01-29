function switchImage(src, element) {
    // Update main image
    const mainImg = document.getElementById('main-product-image');
    if (!mainImg) return;

    // Add a quick fade out effect
    mainImg.style.opacity = '0';

    setTimeout(() => {
        mainImg.src = src;
        mainImg.style.opacity = '1';
    }, 200);

    // Update active thumbnail
    const thumbs = document.querySelectorAll('.thumb-item');
    thumbs.forEach(thumb => thumb.classList.remove('active'));
    element.classList.add('active');
}

document.addEventListener('DOMContentLoaded', () => {
    const mainImg = document.getElementById('main-product-image');
    if (mainImg) {
        // Initialize opacity transition
        mainImg.style.transition = 'opacity 0.2s ease';
    }

    // --- Add to Cart AJAX ---
    const addToCartForm = document.getElementById('add-to-cart-form');
    if (addToCartForm) {
        addToCartForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = document.getElementById('add-to-cart-btn');

            // Disable button during request
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Adding...';

            fetch('cart_handler.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            title: 'Success!',
                            text: data.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        // Update header badge
                        if (typeof updateCartBadge === 'function') {
                            updateCartBadge(data.cart_count);
                        }

                        // Sync to LocalStorage
                        if (typeof saveCartToLocal === 'function' && data.cart_data) {
                            saveCartToLocal(data.cart_data);
                        }
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message,
                            icon: 'error'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Something went wrong!', 'error');
                })
                .finally(() => {
                    // Restore button state
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="ri-shopping-cart-line"></i> Add to Cart';
                });
        });
    }
});
