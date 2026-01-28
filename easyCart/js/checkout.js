document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('checkout-form');
    if (!form) return;

    const inputs = form.querySelectorAll('input:not([type="radio"]), textarea');

    // Get subtotal from data attribute on the form
    const originalSubtotal = parseFloat(form.dataset.subtotal || 0);
    const shippingRadios = document.querySelectorAll('input[name="shipping_method"]');
    const shippingDisplay = document.getElementById('summary-shipping');
    const taxDisplay = document.getElementById('summary-tax');
    const totalDisplay = document.getElementById('summary-total');
    const couponInput = document.getElementById('coupon_code');
    const applyCouponBtn = document.getElementById('apply_coupon');





    function removeCoupon() {
        showDiscountRow(false);

        hideCouponMessage();
    }

    function showCouponMessage(message, type) {
        // Remove existing message
        const existingMessage = document.querySelector('.coupon-message');
        if (existingMessage) {
            existingMessage.remove();
        }

        // Create new message
        const messageDiv = document.createElement('div');
        messageDiv.className = 'coupon-message';
        messageDiv.style.marginTop = '8px';
        messageDiv.style.fontSize = '14px';
        messageDiv.style.color = type === 'success' ? '#10b981' : '#ef4444';
        messageDiv.textContent = message;

        // Insert after coupon input group
        const couponInputGroup = document.querySelector('.coupon-input-group');
        couponInputGroup.parentNode.insertBefore(messageDiv, couponInputGroup.nextSibling);
    }

    function hideCouponMessage() {
        const message = document.querySelector('.coupon-message');
        if (message) {
            message.remove();
        }
    }

    function showDiscountRow(show, percentage = '5%') {
        const discountRow = document.querySelector('.discount-row');
        if (show && !discountRow) {
            // Create discount row
            const summaryTotals = document.querySelector('.summary-totals');
            const subtotalRow = summaryTotals.querySelector('.row:first-child');
            const discountRowElement = document.createElement('div');
            discountRowElement.className = 'row discount-row';
            discountRowElement.innerHTML = `<span>Discount (${percentage})</span><span id="summary-discount">-₹0</span>`;
            subtotalRow.insertAdjacentElement('afterend', discountRowElement);
        } else if (!show && discountRow) {
            discountRow.remove();
        } else if (show && discountRow) {
            // Update existing discount row percentage
            const discountLabel = discountRow.querySelector('span:first-child');
            if (discountLabel) {
                discountLabel.textContent = `Discount (${percentage})`;
            }
        }
    }

    function validateField(input) {
        const errorSpan = document.getElementById(input.id + '-error');
        if (!errorSpan) return true; // If no error span, we don't validate it this way (e.g. coupon_code)

        let errorMessage = "";
        const val = input.value.trim();

        // Required Check
        if (input.required && val === "") {
            errorMessage = "This field is required.";
        } else {
            // Field Specific Checks
            switch (input.id) {
                case 'name':
                    if (val.length < 3) {
                        errorMessage = "Name must be at least 3 characters long.";
                    } else if (!/^[a-zA-Z\s]+$/.test(val)) {
                        errorMessage = "Name should only contain letters and spaces.";
                    }
                    break;
                case 'mobile':
                    if (!/^(\+91)[6-9][0-9]{9}$/.test(val)) {
                        errorMessage = "Please enter a valid Indian mobile number starting with +91.";
                    }
                    break;
                case 'email':
                    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                    if (!emailRegex.test(val)) {
                        errorMessage = "Please enter a valid email address.";
                    }
                    break;
                case 'address':
                    if (val.length < 10) {
                        errorMessage = "Please provide a more detailed address (at least 10 chars).";
                    }
                    break;
                case 'city':
                    if (val.length < 2) {
                        errorMessage = "Please enter a valid city name.";
                    }
                    break;
                case 'pincode':
                    if (!/^[1-9][0-9]{5}$/.test(val)) {
                        errorMessage = "Please enter a valid 6-digit Indian Pincode.";
                    }
                    break;
            }
        }

        if (errorMessage) {
            input.setCustomValidity(errorMessage);
            errorSpan.textContent = errorMessage;
            errorSpan.style.display = 'block';
            input.style.borderColor = '#ef4444';
        } else {
            input.setCustomValidity("");
            errorSpan.style.display = 'none';
            input.style.borderColor = '';
        }

        return !errorMessage;
    }

    function updateSummary() {
        const selectedMethod = document.querySelector('input[name="shipping_method"]:checked').value;
        const couponCode = couponInput ? couponInput.value.trim() : '';

        const formData = new FormData();
        formData.append('action', 'calculate_shipping');
        formData.append('shipping_method', selectedMethod);
        formData.append('subtotal', originalSubtotal);
        formData.append('coupon_code', couponCode);

        fetch('cart_handler.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Update the summary display with data from backend
                    shippingDisplay.textContent = `₹${data.shipping_formatted}`;
                    taxDisplay.textContent = `₹${data.gst_formatted}`;
                    totalDisplay.textContent = `₹${data.total_formatted}`;

                    // Handle Coupon Feedback
                    if (data.coupon_message) {
                        showCouponMessage(data.coupon_message, data.coupon_valid ? 'success' : 'error');
                    } else if (couponCode === '') {
                        hideCouponMessage();
                    }

                    // Handle Discount Row Visibility
                    if (data.coupon_valid) {
                        showDiscountRow(true, data.discount_pct + '%');
                        const discountDisplay = document.getElementById('summary-discount');
                        if (discountDisplay) {
                            discountDisplay.textContent = `-₹${data.discount_formatted}`;
                        }
                    } else {
                        showDiscountRow(false);
                    }
                }
            })
            .catch(error => console.error('Error:', error));
    }

    inputs.forEach(input => {
        input.addEventListener('blur', () => {
            validateField(input);
        });
        input.addEventListener('input', () => {
            const errorSpan = document.getElementById(input.id + '-error');
            if (errorSpan && errorSpan.style.display === 'block') {
                validateField(input);
            }
        });
    });

    shippingRadios.forEach(radio => {
        radio.addEventListener('change', updateSummary);
    });

    // Coupon functionality
    if (applyCouponBtn && couponInput) {
        applyCouponBtn.addEventListener('click', () => {
            updateSummary();
        });

        couponInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyCouponBtn.click();
            }
        });

        couponInput.addEventListener('input', () => {
            if (couponInput.value.trim() === '') {
                removeCoupon();
                hideCouponMessage();
                updateSummary();
            }
        });
    }

    form.addEventListener('submit', (e) => {
        let isFormValid = true;
        inputs.forEach(input => {
            if (!validateField(input)) {
                isFormValid = false;
            }
        });

        if (!isFormValid) {
            e.preventDefault();
            const firstError = form.querySelector('.error-message[style*="display: block"]');
            if (firstError) {
                firstError.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        } else {
            // Place order via AJAX
            e.preventDefault();

            const formData = new FormData(form);
            formData.append('action', 'place_order');

            // Show loading state
            Swal.fire({
                title: 'Placing Order...',
                text: 'Please wait while we process your order.',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('cart_handler.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Redirect to orders page
                        window.location.href = 'orders.php';
                    } else {
                        Swal.fire('Error', data.message || 'Failed to place order', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'An unexpected error occurred.', 'error');
                });
        }
    });
});
