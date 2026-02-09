document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('profile-form');
    const saveBtn = document.getElementById('save-btn');

    if (!form) return;

    // Validation
    function validateField(input) {
        const errorSpan = document.getElementById(input.id + '-error');
        if (!errorSpan) return true;

        let msg = '';
        const val = input.value.trim();

        if (input.required && val === '') {
            msg = 'This field is required.';
        } else if (val !== '') {
            switch (input.id) {
                case 'name':
                    if (val.length < 3 || !/^[a-zA-Z\s]+$/.test(val)) {
                        msg = 'Name must be at least 3 letters.';
                    }
                    break;
                case 'email':
                    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                        msg = 'Enter a valid email.';
                    }
                    break;
                case 'mobile':
                    if (!/^(\+91)[6-9][0-9]{9}$/.test(val)) {
                        msg = 'Enter valid +91 number.';
                    }
                    break;
                case 'pincode':
                    if (!/^[1-9][0-9]{5}$/.test(val)) {
                        msg = 'Enter valid 6-digit pincode.';
                    }
                    break;
                case 'new_password':
                    if (val.length > 0 && val.length < 8) {
                        msg = 'Min 8 characters.';
                    }
                    break;
            }
        }

        if (msg) {
            errorSpan.textContent = msg;
            errorSpan.style.display = 'block';
            input.style.borderColor = '#ef4444';
            return false;
        } else {
            errorSpan.style.display = 'none';
            input.style.borderColor = '';
            return true;
        }
    }

    // Attach validation to inputs
    form.querySelectorAll('input, textarea').forEach(input => {
        input.addEventListener('blur', () => validateField(input));
        input.addEventListener('input', () => {
            const errorSpan = document.getElementById(input.id + '-error');
            if (errorSpan && errorSpan.style.display === 'block') {
                validateField(input);
            }
        });
    });

    // Form submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Validate all
        let isValid = true;
        form.querySelectorAll('input, textarea').forEach(input => {
            if (!validateField(input)) isValid = false;
        });

        if (!isValid) return;

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Saving...';

        const formData = new FormData(form);

        try {
            const response = await fetch('src/handlers/profile.handler', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Profile Updated',
                    text: data.message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });

                // Clear password fields
                document.getElementById('current_password').value = '';
                document.getElementById('new_password').value = '';
            } else {
                Swal.fire('Error', data.message.replace(/\n/g, '<br>'), 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Failed to update profile.', 'error');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="ri-save-line"></i> Save Changes';
        }
    });
});
