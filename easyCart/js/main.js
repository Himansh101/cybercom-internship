document.addEventListener('DOMContentLoaded', () => {
    // --- 1. Mobile Menu Toggle Logic ---
    const menuBtn = document.getElementById('mobile-menu-btn');
    const nav = document.querySelector('nav');

    if (menuBtn && nav) {
        const icon = menuBtn.querySelector('i');

        const toggleMenu = (isOpen) => {
            nav.classList.toggle('active', isOpen);
            if (isOpen) {
                icon.classList.replace('ri-menu-line', 'ri-close-line');
                document.body.style.overflow = 'hidden';
            } else {
                icon.classList.replace('ri-close-line', 'ri-menu-line');
                document.body.style.overflow = '';
            }
        };

        menuBtn.addEventListener('click', () => {
            const isOpening = !nav.classList.contains('active');
            toggleMenu(isOpening);
        });

        // Close menu when clicking a link (useful for one-page sections)
        nav.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => toggleMenu(false));
        });
    }

    // --- 2. Cart Delete Confirmation (Event Delegation) ---
    const cartTable = document.querySelector('table');

    if (cartTable) {
        cartTable.addEventListener('click', function(e) {
            // Target the button or the icon inside the button
            const deleteBtn = e.target.closest('.js-delete-confirm');
            
            if (deleteBtn) {
                e.preventDefault();
                const productName = deleteBtn.getAttribute('data-name');
                const form = deleteBtn.closest('form');

                Swal.fire({
                    title: 'Remove Item?',
                    text: `Are you sure you want to remove "${productName}" from your cart?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e11d48', 
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, remove it!',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Create the hidden input so PHP recognizes the 'remove' action
                        const hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'remove';
                        hiddenInput.value = 'true';
                        form.appendChild(hiddenInput);
                        
                        form.submit();
                    }
                });
            }
        });
    }
});