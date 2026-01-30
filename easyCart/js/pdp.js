document.addEventListener('DOMContentLoaded', () => {
    const mainImg = document.getElementById('main-product-image');
    if (mainImg) {
        mainImg.style.transition = 'opacity 0.2s ease';
    }

    const actionContainer = document.getElementById('cart-action-container');
    if (!actionContainer) return;

    // --- Action Button Handlers (Event Delegation) ---
    actionContainer.addEventListener('submit', function (e) {
        if (e.target.id === 'add-to-cart-form') {
            e.preventDefault();
            const formData = new FormData(e.target);
            const submitBtn = e.target.querySelector('button[type="submit"]');

            submitBtn.disabled = true;
            const originalHTML = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i>';

            fetch('cart_handler.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        updatePDPActionUI(data, formData.get('product_id'));

                        // Toast notification for added to cart
                        Swal.fire({
                            title: 'Added to Cart!',
                            text: 'Product successfully added to your shopping cart.',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end',
                            timerProgressBar: true
                        });

                        if (typeof updateCartBadge === 'function') updateCartBadge(data.cart_count);
                        if (typeof saveCartToLocal === 'function' && data.cart_data) saveCartToLocal(data.cart_data);
                    } else {
                        Swal.fire('Error', data.message, 'error');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalHTML;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHTML;
                });
        }
    });

    actionContainer.addEventListener('click', function (e) {
        const qtyBtn = e.target.closest('.js-pdp-qty-btn');
        if (qtyBtn) {
            const id = qtyBtn.dataset.id;
            const action = qtyBtn.dataset.action;
            const currentQty = parseInt(document.querySelector('.js-pdp-qty-value').textContent);

            if (action === 'minus' && currentQty === 1) {
                // Confirm removal if dropping to 0
                Swal.fire({
                    title: 'Remove from cart?',
                    text: 'This will remove the item from your cart.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, remove it',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        updatePDPQuantity(id, 'minus');
                    }
                });
            } else {
                updatePDPQuantity(id, action);
            }
        }
    });

    function updatePDPQuantity(id, action) {
        const formData = new FormData();
        formData.append('cart_action_source', 'pdp'); // Flag for context
        formData.append('action', 'update');
        formData.append('product_id', id);
        formData.append('qty_action', action);

        fetch('cart_handler.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    updatePDPActionUI(data, id);

                    // Show notification for addition
                    if (action === 'plus') {
                        Swal.fire({
                            title: 'Quantity Updated!',
                            text: 'Item quantity increased in your cart.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end',
                            timerProgressBar: true
                        });
                    }

                    if (typeof updateCartBadge === 'function') updateCartBadge(data.cart_count);
                    if (typeof saveCartToLocal === 'function' && data.cart_data) saveCartToLocal(data.cart_data);
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function updatePDPActionUI(data, productId) {
        const itemData = data.items ? data.items[productId] : null;

        if (!itemData) {
            // Item removed or not in cart - Show Add to Cart Button
            actionContainer.innerHTML = `
                <form id="add-to-cart-form" method="POST">
                    <input type="hidden" name="product_id" value="${productId}">
                    <input type="hidden" name="action" value="add">
                    <button type="submit" id="add-to-cart-btn" class="btn btn-success" style="width: 100%; justify-content: center; display: flex; align-items: center; gap: 8px;">
                        <i class="ri-shopping-cart-line"></i> Add to Cart
                    </button>
                </form>
            `;
        } else {
            // Show Quantity Controls
            actionContainer.innerHTML = `
                <div class="pdp-qty-control" style="display: flex; align-items: center; gap: 15px; background: #f8fafc; padding: 10px 20px; border-radius: 12px; border: 1px solid #e2e8f0; width: fit-content;">
                    <button type="button" class="btn-qty minus js-pdp-qty-btn" data-action="minus" data-id="${productId}" style="width: 40px; height: 40px; border-radius: 50%; border: 1px solid #cbd5e1; background: white; font-size: 1.2rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                        <i class="ri-subtract-line"></i>
                    </button>
                    <span class="js-pdp-qty-value" style="font-size: 1.2rem; font-weight: 700; min-width: 30px; text-align: center; color: #1e293b;">
                        ${itemData.quantity}
                    </span>
                    <button type="button" class="btn-qty plus js-pdp-qty-btn" data-action="plus" data-id="${productId}" 
                        style="width: 40px; height: 40px; border-radius: 50%; border: 1px solid #cbd5e1; background: white; font-size: 1.2rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; ${itemData.is_maxed ? 'opacity: 0.5; cursor: not-allowed;' : ''}"
                        ${itemData.is_maxed ? 'disabled' : ''}>
                        <i class="ri-add-line"></i>
                    </button>
                </div>
                <div class="pdp-stock-warning" style="margin-top: 8px; font-size: 0.8rem; color: #e11d48; font-weight: 500;">
                    ${itemData.is_maxed ? 'Max stock reached' : ''}
                </div>
            `;
        }
    }
});

function switchImage(src, element) {
    const mainImg = document.getElementById('main-product-image');
    if (!mainImg) return;

    mainImg.style.opacity = '0';
    setTimeout(() => {
        mainImg.src = src;
        mainImg.style.opacity = '1';
    }, 200);

    const thumbs = document.querySelectorAll('.thumb-item');
    thumbs.forEach(thumb => thumb.classList.remove('active'));
    element.classList.add('active');
}
