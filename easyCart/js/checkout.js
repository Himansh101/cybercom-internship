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

    let currentDiscount = 0;
    let currentSubtotal = originalSubtotal;

    function calculateShipping(method, subtotal) {
        switch (method) {
            case 'standard':
                return 40; // Flat $40
            case 'express':
                return Math.min(80, subtotal * 0.10); // Flat $80 OR 10% of subtotal (whichever is lower)
            case 'white_glove':
                return Math.min(150, subtotal * 0.05); // Flat $150 OR 5% of subtotal (whichever is lower)
            case 'freight':
                return Math.max(200, subtotal * 0.03); // 3% of subtotal, Minimum $200
            default:
                return 40;
        }
    }

    function applyCoupon(couponCode) {
        const code = couponCode.toUpperCase();
        switch (code) {
            case 'SAVE5':
                currentDiscount = originalSubtotal * 0.05;
                currentSubtotal = originalSubtotal - currentDiscount;
                showCouponMessage('5% discount applied!', 'success');
                showDiscountRow(true, '5%');
                return true;
            case 'SAVE10':
                currentDiscount = originalSubtotal * 0.10;
                currentSubtotal = originalSubtotal - currentDiscount;
                showCouponMessage('10% discount applied!', 'success');
                showDiscountRow(true, '10%');
                return true;
            case 'SAVE15':
                currentDiscount = originalSubtotal * 0.15;
                currentSubtotal = originalSubtotal - currentDiscount;
                showCouponMessage('15% discount applied!', 'success');
                showDiscountRow(true, '15%');
                return true;
            case 'SAVE20':
                currentDiscount = originalSubtotal * 0.20;
                currentSubtotal = originalSubtotal - currentDiscount;
                showCouponMessage('20% discount applied!', 'success');
                showDiscountRow(true, '20%');
                return true;
            default:
                if (couponCode.trim() !== '') {
                    showCouponMessage('Invalid coupon code', 'error');
                }
                return false;
        }
    }

    function removeCoupon() {
        currentDiscount = 0;
        currentSubtotal = originalSubtotal;
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
        if (!errorSpan) return;

        if (!input.checkValidity()) {
            errorSpan.style.display = 'block';
            input.style.borderColor = '#ef4444';
        } else {
            errorSpan.style.display = 'none';
            input.style.borderColor = '';
        }
    }

    function updateSummary() {
        const selectedMethod = document.querySelector('input[name="shipping_method"]:checked').value;
        const shippingCost = calculateShipping(selectedMethod, currentSubtotal);
        const gst = currentSubtotal * 0.18; // 18% GST on discounted subtotal
        const total = currentSubtotal + shippingCost + gst;

        // Update the summary display with formatted numbers
        shippingDisplay.textContent = `₹${shippingCost.toLocaleString()}`;
        taxDisplay.textContent = `₹${gst.toLocaleString()}`;
        totalDisplay.textContent = `₹${total.toLocaleString()}`;

        // Update discount display if discount row exists
        const discountDisplay = document.getElementById('summary-discount');
        if (discountDisplay) {
            discountDisplay.textContent = `-₹${currentDiscount.toLocaleString()}`;
        }
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
            const couponCode = couponInput.value.trim();
            if (couponCode === '') {
                removeCoupon();
                hideCouponMessage();
            } else {
                applyCoupon(couponCode);
            }
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
        let isValid = true;
        inputs.forEach(input => {
            validateField(input);
            if (!input.checkValidity()) {
                isValid = false;
            }
        });

        if (!isValid) {
            e.preventDefault();
            const firstError = form.querySelector('.error-message[style*="display: block"]');
            if (firstError) {
                firstError.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        }
    });
});
