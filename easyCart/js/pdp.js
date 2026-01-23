function switchImage(src, element) {
    // Update main image
    const mainImg = document.getElementById('main-product-image');
    if (!mainImg) return;

    // Add a quick fade out effect
    mainImg.style.opacity = '0';

    setTimeout(() => {
        mainImg.src = src;
        mainImg.style.opacity = '1';
    }, 200);

    // Update active thumbnail
    const thumbs = document.querySelectorAll('.thumb-item');
    thumbs.forEach(thumb => thumb.classList.remove('active'));
    element.classList.add('active');
}

document.addEventListener('DOMContentLoaded', () => {
    const mainImg = document.getElementById('main-product-image');
    if (mainImg) {
        // Initialize opacity transition
        mainImg.style.transition = 'opacity 0.2s ease';
    }
});
