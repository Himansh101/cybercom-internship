document.addEventListener('DOMContentLoaded', () => {
    // 1. Mobile Menu Toggle Logic
    const menuBtn = document.getElementById('mobile-menu-btn');
    const nav = document.querySelector('nav');

    if (menuBtn && nav) {
        menuBtn.addEventListener('click', () => {
            nav.classList.toggle('active');

            // Switch icon between menu and close
            const icon = menuBtn.querySelector('i');
            if (nav.classList.contains('active')) {
                icon.classList.replace('ri-menu-line', 'ri-close-line');
                document.body.style.overflow = 'hidden'; // Prevent scrolling when menu is open
            } else {
                icon.classList.replace('ri-close-line', 'ri-menu-line');
                document.body.style.overflow = '';
            }
        });

        // Close menu when clicking a link
        nav.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                nav.classList.remove('active');
                const icon = menuBtn.querySelector('i');
                icon.classList.replace('ri-close-line', 'ri-menu-line');
                document.body.style.overflow = '';
            });
        });
    }
});
