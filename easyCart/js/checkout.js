document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('checkout-form');
    if (!form) return;

    const inputs = form.querySelectorAll('input:not([type="radio"]), textarea');

    // Get subtotal from data attribute on the form
    const subtotal = parseFloat(form.dataset.subtotal || 0);
    const shippingRadios = document.querySelectorAll('input[name="shipping_method"]');
    const shippingDisplay = document.getElementById('summary-shipping');
    const totalDisplay = document.getElementById('summary-total');

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
        const shippingCost = selectedMethod === 'fast' ? 150 : 90;
        const total = subtotal + shippingCost;

        // Update the summary display with formatted numbers
        shippingDisplay.textContent = `₹${shippingCost.toLocaleString()}`;
        totalDisplay.textContent = `₹${total.toLocaleString()}`;
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
