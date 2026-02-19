document.addEventListener('DOMContentLoaded', function () {
    // 1. Gallery Switcher
    const mainImage = document.querySelector('.main-image-wrapper img');
    const thumbnails = document.querySelectorAll('.gallery-item img');

    thumbnails.forEach(thumb => {
        thumb.parentElement.addEventListener('click', function () {
            // Update main image src
            const newSrc = thumb.getAttribute('src');
            mainImage.setAttribute('src', newSrc);

            // Update active state styling
            document.querySelectorAll('.gallery-item').forEach(item => {
                item.style.borderColor = '#e2e8f0';
            });
            thumb.parentElement.style.borderColor = '#2563eb';
        });
    });

    // 2. Add to Cart Interaction
    const addToCartBtn = document.querySelector('.btn-add-to-cart');
    const qtyInput = document.querySelector('.qty-selector');

    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function () {
            const qty = qtyInput.value;
            const productName = document.querySelector('.product-title').innerText;

            if (qty < 1) {
                alert('Please enter a valid quantity.');
                return;
            }

            alert(`Success! Added ${qty} x "${productName}" to your cart.`);

            // You can implement actual AJAX logic here
            // console.log('Product ID: 1', 'Quantity:', qty);
        });
    }

    // 3. Simple Quantity Validation on input
    if (qtyInput) {
        qtyInput.addEventListener('change', function () {
            if (this.value < 1) this.value = 1;
        });
    }
});
