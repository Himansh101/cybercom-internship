document.addEventListener('DOMContentLoaded', () => {
    // --- 1. Logout Confirmation Logic ---
    const logoutLink = document.querySelector('a[href="logout.php"]');
    if (logoutLink && typeof Swal !== 'undefined') {
        logoutLink.addEventListener('click', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Logout?',
                text: 'Are you sure you want to logout?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, logout!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'logout.php';
                }
            });
        });
    }

    // 1. Common Validation Function
    function validateField(input) {
        const errorSpan = document.getElementById(input.id + '-error');
        if (!errorSpan) return true;

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
                case 'email':
                    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                    if (!emailRegex.test(val)) {
                        errorMessage = "Please enter a valid email address (e.g., alex@example.com).";
                    }
                    break;
                case 'mobile':
                    if (!/^[6-9][0-9]{9}$/.test(val)) {
                        errorMessage = "Please enter a valid 10-digit Indian mobile number.";
                    }
                    break;
                case 'password':
                    if (input.form.id === 'signupForm') {
                        if (val.length < 8) {
                            errorMessage = "Password must be at least 8 characters long.";
                        } else if (!/[a-z]/.test(val)) {
                            errorMessage = "Password must contain at least one lowercase letter.";
                        } else if (!/[A-Z]/.test(val)) {
                            errorMessage = "Password must contain at least one uppercase letter.";
                        } else if (!/[0-9]/.test(val)) {
                            errorMessage = "Password must contain at least one number.";
                        }
                    } else {
                        // Login form basic length check
                        if (val.length < 1) {
                            errorMessage = "Password is required.";
                        }
                    }
                    break;
                case 'confirm':
                    const password = document.getElementById('password');
                    if (password && password.value !== val) {
                        errorMessage = "Passwords do not match.";
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

    // 2. Identify which form is present
    const loginForm = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');

    if (loginForm) {
        setupValidation(loginForm);
    }
    if (signupForm) {
        setupValidation(signupForm);
    }

    function setupValidation(form) {
        const inputs = form.querySelectorAll('input');

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

        form.addEventListener('submit', (e) => {
            let isFormValid = true;
            inputs.forEach(input => {
                if (!validateField(input)) {
                    isFormValid = false;
                }
            });

            if (!isFormValid) {
                e.preventDefault();
                // Find first error and scroll to it
                const firstError = form.querySelector('.error-message[style*="display: block"]');
                if (firstError) {
                    firstError.parentElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    }
});
