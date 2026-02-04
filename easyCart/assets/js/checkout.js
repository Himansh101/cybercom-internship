document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('checkout-form');
    if (!form) return;

    const inputs = form.querySelectorAll('input:not([type="radio"]), textarea');
    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
    const stripeContainer = document.getElementById('stripe-card-container');
    const submitBtn = document.querySelector('button[type="submit"]');
    const shippingRadios = document.querySelectorAll('input[name="shipping_method"]');
    const shippingDisplay = document.getElementById('summary-shipping');
    const taxDisplay = document.getElementById('summary-tax');
    const totalDisplay = document.getElementById('summary-total');
    const couponInput = document.getElementById('coupon_code');
    const applyCouponBtn = document.getElementById('apply_coupon');

    // --- Stripe Initialization ---
    let stripe, elements, card;
    if (typeof Stripe !== 'undefined' && typeof STRIPE_PUBLISHABLE_KEY !== 'undefined') {
        stripe = Stripe(STRIPE_PUBLISHABLE_KEY);
        elements = stripe.elements();
        card = elements.create('card', {
            style: {
                base: {
                    color: "#32325d",
                    fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                    fontSmoothing: "antialiased",
                    fontSize: "16px",
                    "::placeholder": { color: "#aab7c4" }
                },
                invalid: { color: "#fa755a", iconColor: "#fa755a" }
            }
        });
        card.mount("#card-element");

        card.on('change', ({ error }) => {
            const displayError = document.getElementById('card-errors');
            if (error) {
                displayError.textContent = error.message;
            } else {
                displayError.textContent = '';
            }
        });
    }

    // Toggle Stripe Container
    function toggleStripeContainer() {
        const selected = document.querySelector('input[name="payment_method"]:checked');
        if (selected && selected.value === 'stripe') {
            stripeContainer.classList.remove('hidden');
            stripeContainer.style.display = 'block';
        } else {
            stripeContainer.classList.add('hidden');
            stripeContainer.style.display = 'none';
        }
        // Sync with backend
        updateSummary();
    }

    paymentRadios.forEach(radio => {
        radio.addEventListener('change', toggleStripeContainer);
    });

    // Set initial state
    toggleStripeContainer();

    // --- Reusing Existing Logic for Summary/Validation ---

    function updateSummary() {
        const selectedRadio = document.querySelector('input[name="shipping_method"]:checked');
        if (!selectedRadio) return;

        const selectedMethod = selectedRadio.value;
        const couponCode = couponInput ? couponInput.value.trim() : '';
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'cod';

        const formData = new FormData();
        formData.append('action', 'calculate_shipping');
        formData.append('shipping_method', selectedMethod);
        formData.append('coupon_code', couponCode);
        formData.append('payment_method', paymentMethod);

        fetch('src/controllers/checkout.handler', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    if (data.allowed_methods) {
                        updateShippingAvailability(data.allowed_methods, data.shipping_info_message, data.selected_method);
                    }
                    shippingDisplay.textContent = `₹${data.shipping_formatted}`;
                    taxDisplay.textContent = `₹${data.gst_formatted}`;
                    totalDisplay.textContent = `₹${data.total_formatted}`;

                    if (data.coupon_message) {
                        showCouponMessage(data.coupon_message, data.coupon_valid ? 'success' : 'error');
                    } else if (couponCode === '') {
                        hideCouponMessage();
                    }

                    if (data.coupon_valid) {
                        showDiscountRow(true, data.discount_pct + '%');
                        const discountDisplay = document.getElementById('summary-discount');
                        if (discountDisplay) discountDisplay.textContent = `-₹${data.discount_formatted}`;
                        if (applyCouponBtn) { applyCouponBtn.textContent = 'Remove'; applyCouponBtn.dataset.state = 'remove'; }
                    } else {
                        showDiscountRow(false);
                        if (applyCouponBtn) { applyCouponBtn.textContent = 'Apply'; applyCouponBtn.dataset.state = 'apply'; }
                    }
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // --- Form Submission Logic ---
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // 1. Validate Fields
        let isFormValid = true;
        inputs.forEach(input => {
            if (!validateField(input)) isFormValid = false;
        });

        if (!isFormValid) {
            const firstError = form.querySelector('.error-message[style*="display: block"]');
            if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

        // 2. Handle Stripe
        if (paymentMethod === 'stripe') {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Processing...';

            try {
                // A. Create Payment Intent
                const intentRes = await fetch('src/controllers/checkout.handler', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=create_payment_intent'
                }).then(r => r.json());

                if (intentRes.status !== 'success') {
                    throw new Error(intentRes.message);
                }

                // B. Confirm Card Payment
                const result = await stripe.confirmCardPayment(intentRes.clientSecret, {
                    payment_method: {
                        card: card,
                        billing_details: {
                            name: document.getElementById('name').value,
                            email: document.getElementById('email').value
                        }
                    }
                });

                if (result.error) {
                    throw new Error(result.error.message);
                } else {
                    if (result.paymentIntent.status === 'succeeded') {
                        // C. Submit Order with Intent ID
                        placeOrder(paymentMethod, result.paymentIntent.id);
                    }
                }

            } catch (err) {
                console.error(err);
                Swal.fire('Payment Failed', err.message, 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Place Order';
            }
        } else {
            // COD
            placeOrder(paymentMethod);
        }
    });

    function placeOrder(paymentMethod, paymentIntentId = null) {
        const formData = new FormData(form);
        formData.append('action', 'place_order');
        formData.append('is_ajax', '1');
        formData.append('payment_method', paymentMethod);
        if (paymentIntentId) {
            formData.append('payment_intent_id', paymentIntentId);
        }

        Swal.fire({
            title: 'Placing Order...',
            text: 'Please wait while we process your order.',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => { Swal.showLoading(); }
        });

        fetch('src/controllers/checkout.handler', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    localStorage.removeItem('guest_cart');
                    Swal.fire({
                        icon: 'success',
                        title: 'Order Placed Successfully',
                        text: 'Redirecting to your orders...',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = 'orders';
                    });
                } else {
                    Swal.fire('Error', data.message || 'Failed to place order', 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Place Order';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'An unexpected error occurred.', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Place Order';
            });
    }

    // --- Helper Functions (Validation, Coupons, etc.) Same as before ---
    function removeCoupon() { showDiscountRow(false); hideCouponMessage(); }
    function showCouponMessage(msg, type) {
        const existing = document.querySelector('.coupon-message');
        if (existing) existing.remove();
        const div = document.createElement('div');
        div.className = 'coupon-message';
        div.style.marginTop = '8px';
        div.style.fontSize = '14px';
        div.style.color = type === 'success' ? '#10b981' : '#ef4444';
        div.textContent = msg;
        const group = document.querySelector('.coupon-input-group');
        group.parentNode.insertBefore(div, group.nextSibling);
    }
    function hideCouponMessage() { const msg = document.querySelector('.coupon-message'); if (msg) msg.remove(); }
    function showDiscountRow(show, percentage = '5%') {
        const row = document.querySelector('.discount-row');
        if (show && !row) {
            const summaryTotals = document.querySelector('.summary-totals');
            const subtotalRow = summaryTotals.querySelector('.row:first-child');
            const div = document.createElement('div');
            div.className = 'row discount-row';
            div.innerHTML = `<span>Discount (${percentage})</span><span id="summary-discount">-₹0</span>`;
            subtotalRow.insertAdjacentElement('afterend', div);
        } else if (!show && row) row.remove();
        else if (show && row) row.querySelector('span:first-child').textContent = `Discount (${percentage})`;
    }
    function validateField(input) {
        const errorSpan = document.getElementById(input.id + '-error');
        if (!errorSpan) return true;
        let msg = "";
        const val = input.value.trim();
        if (input.required && val === "") msg = "This field is required.";
        else {
            if (input.id === 'name' && (val.length < 3 || !/^[a-zA-Z\s]+$/.test(val))) msg = "Name must be at least 3 chars (letters only).";
            if (input.id === 'mobile' && !/^(\+91)[6-9][0-9]{9}$/.test(val)) msg = "Enter valid +91 number.";
            if (input.id === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) msg = "Enter valid email.";
            if (input.id === 'address' && val.length < 10) msg = "Address too short (min 10 chars).";
            if (input.id === 'city' && val.length < 2) msg = "Enter valid city.";
            if (input.id === 'pincode' && !/^[1-9][0-9]{5}$/.test(val)) msg = "Enter valid 6-digit Pincode.";
        }
        if (msg) { errorSpan.textContent = msg; errorSpan.style.display = 'block'; input.style.borderColor = '#ef4444'; return false; }
        else { errorSpan.style.display = 'none'; input.style.borderColor = ''; return true; }
    }
    function updateShippingAvailability(methods, msg, selected) {
        ['standard', 'express', 'white_glove', 'freight'].forEach(m => {
            const radio = document.querySelector(`input[name="shipping_method"][value="${m}"]`);
            const label = radio ? radio.closest('.shipping-card') : null;
            if (radio && label) {
                const allowed = methods.includes(m);
                radio.disabled = !allowed;
                allowed ? label.classList.remove('disabled') : label.classList.add('disabled');
            }
        });
        if (selected) {
            const r = document.querySelector(`input[name="shipping_method"][value="${selected}"]`);
            if (r && !r.disabled) r.checked = true;
        }
        if (msg) document.querySelector('.shipping-header p').innerHTML = `<i class="ri-information-line"></i> ${msg}`;
    }

    inputs.forEach(i => {
        i.addEventListener('blur', () => validateField(i));
        i.addEventListener('input', () => {
            const errorSpan = document.getElementById(i.id + '-error');
            if (errorSpan && errorSpan.style.display === 'block') validateField(i);
        });
    });
    shippingRadios.forEach(r => r.addEventListener('change', updateSummary));
    if (applyCouponBtn) {
        applyCouponBtn.addEventListener('click', () => {
            if (applyCouponBtn.dataset.state === 'remove') {
                // Remove logic
                const fd = new FormData(); fd.append('action', 'remove_coupon');
                fetch('src/controllers/checkout.handler', { method: 'POST', body: fd }).then(r => r.json()).then(d => {
                    if (d.status === 'success') { couponInput.value = ''; applyCouponBtn.textContent = 'Apply'; applyCouponBtn.dataset.state = 'apply'; removeCoupon(); updateSummary(); Swal.fire({ icon: 'success', title: 'Coupon Removed', toast: true, position: 'top-end', showConfirmButton: false, timer: 2000, timerProgressBar: true }); }
                });
            } else updateSummary();
        });
        couponInput.addEventListener('input', () => { if (couponInput.value.trim() === '') { removeCoupon(); hideCouponMessage(); updateSummary(); } });
    }
    updateSummary();
});
