document.addEventListener('DOMContentLoaded', () => {
    // 1. Common Validation Function
    function validateField(input) {
        const errorSpan = document.getElementById(input.id + '-error');
        if (!errorSpan) return;

        // Custom check for password confirmation (only for signup)
        if (input.id === 'confirm') {
            const password = document.getElementById('password');
            if (password.value !== input.value) {
                input.setCustomValidity("Passwords don't match");
            } else {
                input.setCustomValidity("");
            }
        }

        if (!input.checkValidity()) {
            errorSpan.style.display = 'block';
            input.style.borderColor = '#ef4444';
        } else {
            errorSpan.style.display = 'none';
            input.style.borderColor = '';
        }
    }

    // 2. Identify which form is present
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');
    const currentForm = loginForm || signupForm;

    if (currentForm) {
        const inputs = currentForm.querySelectorAll('input');

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

        currentForm.addEventListener('submit', (e) => {
            let isValid = true;
            inputs.forEach(input => {
                validateField(input);
                if (!input.checkValidity()) {
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
            }
        });
    }
});
