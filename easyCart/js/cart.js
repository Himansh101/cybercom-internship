document.addEventListener('DOMContentLoaded', () => {
    const cartTable = document.querySelector('table');
    if (!cartTable) return;

    // --- Quantity Update Logic ---
    cartTable.addEventListener('click', (e) => {
        const qtyBtn = e.target.closest('.js-qty-btn');
        if (qtyBtn) {
            const id = qtyBtn.getAttribute('data-id');
            const qtyAction = qtyBtn.getAttribute('data-action');
            updateQuantity(id, qtyAction);
        }
    });

    // --- Delete Confirmation ---
    cartTable.addEventListener('click', (e) => {
        const deleteBtn = e.target.closest('.js-delete-confirm');
        if (deleteBtn) {
            e.preventDefault();
            const productName = deleteBtn.getAttribute('data-name');
            const id = deleteBtn.getAttribute('data-id');

            Swal.fire({
                title: 'Remove Item?',
                text: `Are you sure you want to remove "${productName}" from your cart?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, remove it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    removeItem(id);
                }
            });
        }
    });
});

function updateQuantity(id, qtyAction) {
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('product_id', id);
    formData.append('qty_action', qtyAction);

    fetch('cart_handler.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Check if item was removed via minus
                if (data.items && !data.items[id]) {
                    const row = document.querySelector(`tr[data-id="${id}"]`);
                    if (row) {
                        fadeOutAndRemove(row, data);
                    }
                } else {
                    updateCartUI(data);
                }
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => console.error('Error:', error));
}

function removeItem(id) {
    const formData = new FormData();
    formData.append('action', 'remove');
    formData.append('product_id', id);

    fetch('cart_handler.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const row = document.querySelector(`tr[data-id="${id}"]`);
                if (row) {
                    fadeOutAndRemove(row, data);
                }
            }
        })
        .catch(error => console.error('Error:', error));
}

function fadeOutAndRemove(row, data) {
    row.style.transition = 'all 0.3s ease';
    row.style.opacity = '0';
    row.style.transform = 'translateX(20px)';

    setTimeout(() => {
        row.remove();
        // If cart is empty, show empty message without reload if possible
        if (document.querySelectorAll('tbody tr[data-id]').length === 0) {
            const tbody = document.querySelector('tbody');
            if (tbody) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="empty-msg">
                            <i class="ri-shopping-cart-2-line" style="font-size: 3rem; color: #cbd5e1; display: block; margin-bottom: 10px;"></i>
                            Your cart is empty.
                        </td>
                    </tr>
                `;
            }
            // Update summary to 0
            updateCartUI(data);
            // Disable checkout button
            const checkoutBtn = document.getElementById('checkout-link');
            if (checkoutBtn) {
                checkoutBtn.className = 'btn btn-disabled mt-18 w-full';
                checkoutBtn.href = '#';
            }
        } else {
            updateCartUI(data);
        }
    }, 300);
}

function updateCartUI(data) {
    // 1. Update individual item quantities and subtotals
    if (data.items) {
        for (const [id, item] of Object.entries(data.items)) {
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (row) {
                const qtyInput = row.querySelector('.js-qty-input');
                const subtotalCell = row.querySelector('.js-item-subtotal');
                const plusBtn = row.querySelector('.plus');
                const warningArea = row.querySelector('.stock-warning');

                if (qtyInput) qtyInput.value = item.quantity;
                if (subtotalCell) subtotalCell.textContent = item.item_total;

                // Handle max stock state
                if (plusBtn) {
                    if (item.is_maxed) {
                        plusBtn.disabled = true;
                        plusBtn.style.opacity = '0.5';
                        plusBtn.style.cursor = 'not-allowed';
                        if (warningArea && !warningArea.querySelector('small')) {
                            warningArea.innerHTML = '<small style="display:block; color: #e11d48; font-size: 0.7rem; margin-top: 4px;">Max stock reached</small>';
                        }
                    } else {
                        plusBtn.disabled = false;
                        plusBtn.style.opacity = '1';
                        plusBtn.style.cursor = 'pointer';
                        if (warningArea) warningArea.innerHTML = '';
                    }
                }
            }
        }
    }

    // 2. Update summary section
    document.getElementById('cart-subtotal').textContent = data.subtotal;
    document.getElementById('cart-shipping').textContent = data.shipping;
    document.getElementById('cart-total').textContent = data.total;

    // 3. Update header badge
    if (typeof updateCartBadge === 'function') {
        updateCartBadge(data.cart_count);
    }
}
