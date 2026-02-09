document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('orderModal');
    const closeBtn = document.querySelector('.close-modal');
    const modalContent = document.getElementById('modalContent');
    const modalLoading = document.getElementById('modalLoading');
    const modalItems = document.getElementById('modalItems');
    const modalOrderNo = document.getElementById('modalOrderNo');

    // Price fields
    const detailSubtotal = document.getElementById('detailSubtotal');
    const detailShipping = document.getElementById('detailShipping');
    const detailTax = document.getElementById('detailTax');
    const detailTotal = document.getElementById('detailTotal');
    const detailAddress = document.getElementById('detailAddress');

    // Open Modal
    document.querySelectorAll('.btn-details').forEach(btn => {
        btn.addEventListener('click', function () {
            const orderId = this.getAttribute('data-order-id');
            openOrderDetails(orderId);
        });
    });

    // Close Modal
    closeBtn.addEventListener('click', () => modal.style.display = 'none');
    window.addEventListener('click', (e) => {
        if (e.target == modal) modal.style.display = 'none';
    });

    function openOrderDetails(orderId) {
        modal.style.display = 'flex';
        modalLoading.style.display = 'flex';
        modalContent.style.display = 'none';
        modalOrderNo.textContent = '#' + orderId;

        const formData = new FormData();
        formData.append('action', 'get_details');
        formData.append('order_id', orderId);

        fetch('src/handlers/order.handler', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(res => {
                if (res.status === 'success') {
                    populateModal(res.data);
                    modalLoading.style.display = 'none';
                    modalContent.style.display = 'block';
                } else {
                    Swal.fire('Error', res.message || 'Failed to fetch details', 'error');
                    modal.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Connection failed', 'error');
                modal.style.display = 'none';
            });
    }

    function populateModal(data) {
        // Clear items
        modalItems.innerHTML = '';

        // Add Items
        data.items.forEach(item => {
            const itemHtml = `
                <div class="detail-item">
                    <div class="detail-item-img">
                        <img src="${item.image}" alt="${item.name}">
                    </div>
                    <div class="detail-item-info">
                        <h4>${item.name}</h4>
                        <p>₹${numberFormat(item.price)} × ${item.qty}</p>
                    </div>
                    <div class="detail-item-total">
                        ₹${numberFormat(item.total)}
                    </div>
                </div>
            `;
            modalItems.insertAdjacentHTML('beforeend', itemHtml);
        });

        // Update Prices
        detailSubtotal.textContent = '₹' + numberFormat(data.subtotal);
        detailShipping.textContent = '₹' + numberFormat(data.shipping);
        detailTax.textContent = '₹' + numberFormat(data.tax);
        detailTotal.textContent = '₹' + numberFormat(data.total);

        // Update Address
        if (data.address) {
            detailAddress.innerHTML = `
                <strong>${data.address.full_name}</strong><br>
                ${data.address.street_address}<br>
                ${data.address.city} - ${data.address.pincode}
            `;
        } else {
            detailAddress.textContent = 'No address details found.';
        }
    }

    function numberFormat(num) {
        return new Intl.NumberFormat('en-IN').format(num);
    }
});
