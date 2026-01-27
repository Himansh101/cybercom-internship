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

        // Custom email validation for signup and login
        if (input.id === 'email' && (input.form.id === 'signupForm' || input.form.id === 'loginForm')) {
            const email = input.value;
            const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

            if (email && !emailRegex.test(email)) {
                input.setCustomValidity("Please enter a valid email address with a domain extension (e.g., .com, .org, .net)");
            } else {
                input.setCustomValidity("");
            }
        }

        // Custom password validation for signup
        if (input.id === 'password' && input.form.id === 'signupForm') {
            const password = input.value;
            let isValid = true;
            let errorMessage = "";

            if (password.length < 8) {
                isValid = false;
                errorMessage = "Password must be at least 8 characters long.";
            } else if (!/(?=.*[a-z])/.test(password)) {
                isValid = false;
                errorMessage = "Password must contain at least one lowercase letter.";
            } else if (!/(?=.*[A-Z])/.test(password)) {
                isValid = false;
                errorMessage = "Password must contain at least one uppercase letter.";
            } else if (!/(?=.*[0-9])/.test(password)) {
                isValid = false;
                errorMessage = "Password must contain at least one number.";
            }

            if (!isValid) {
                input.setCustomValidity(errorMessage);
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
