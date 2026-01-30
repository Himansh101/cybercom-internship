document.addEventListener('DOMContentLoaded', () => {
    const filterForm = document.getElementById('filter-form');
    const gridContainer = document.getElementById('product-grid-container');
    const paginationContainer = document.getElementById('pagination-container');
    const productCount = document.getElementById('product-count');
    const loadingOverlay = document.getElementById('loading-overlay');
    const sortSelect = document.getElementById('sort');

    if (!filterForm) return;

    let typingTimer;
    const debounceDelay = 500;

    /**
     * core function to fetch and update products
     */
    function updateProducts(resetPage = true) {
        if (resetPage) {
            const pageInput = filterForm.querySelector('input[name="page"]');
            if (pageInput) pageInput.value = '1';
        }

        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        const url = `plp.php?${params.toString()}`;

        // Show loading state
        if (loadingOverlay) loadingOverlay.style.display = 'flex';
        gridContainer.style.opacity = '0.5';

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Update UI
                    gridContainer.innerHTML = loadingOverlay.outerHTML + data.grid_html;
                    paginationContainer.innerHTML = data.pagination_html;
                    productCount.textContent = data.count_text;

                    // Update URL without reload
                    window.history.pushState({ path: url }, '', url);
                }
            })
            .catch(error => console.error('Error fetching products:', error))
            .finally(() => {
                // Hide loading state
                const newLoadingOverlay = document.getElementById('loading-overlay');
                if (newLoadingOverlay) newLoadingOverlay.style.display = 'none';
                gridContainer.style.opacity = '1';
            });
    }

    // Listen for changes in form inputs
    filterForm.addEventListener('change', (e) => {
        if (e.target.type === 'checkbox' || e.target.tagName === 'SELECT') {
            updateProducts(true);
        }
    });

    // Handle sort dropdown specifically (since it might have inline onchange)
    if (sortSelect) {
        sortSelect.removeAttribute('onchange');
        sortSelect.addEventListener('change', () => updateProducts(true));
    }

    // Listen for typing in search and price inputs (Debounced)
    filterForm.addEventListener('input', (e) => {
        if (e.target.type === 'number' || e.target.name === 'search') {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
                updateProducts(true);
            }, debounceDelay);
        }
    });

    // Handle pagination clicks (Event Delegation)
    paginationContainer.addEventListener('click', (e) => {
        const link = e.target.closest('.pagination-btn');
        if (link && link.dataset.page) {
            e.preventDefault();
            const pageInput = filterForm.querySelector('input[name="page"]');
            if (pageInput) {
                pageInput.value = link.dataset.page;
                updateProducts(false); // Don't reset to page 1
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }
    });

    // Handle browser back/forward buttons
    window.addEventListener('popstate', () => {
        // Simple reload for now to sync form state with URL
        // A more complex version would update form fields based on URL params
        window.location.reload();
    });

    // Prevent form submission
    filterForm.addEventListener('submit', (e) => {
        e.preventDefault();
        updateProducts(true);
    });
});
