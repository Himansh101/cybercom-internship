document.addEventListener('DOMContentLoaded',()=>{
    const filterForm = document.getElementById('filter-form');
    console.log(filterForm);


    if (filterForm) {
        const inputs = filterForm.querySelectorAll('.js-filter-input');
        let typingTimer;

        inputs.forEach(input => {

            if (input.type === 'checkbox') {
                input.addEventListener('change', () => {
                    // Reset to page 1 when filters change
                    const pageInput = filterForm.querySelector('input[name="page"]');
                    if (pageInput) {
                        pageInput.value = '1';
                    }
                    filterForm.submit();
                });
            }

            if (input.type === 'number') {
                input.addEventListener('input', () => {
                    clearTimeout(typingTimer);
                    typingTimer = setTimeout(() => {
                        // Reset to page 1 when filters change
                        const pageInput = filterForm.querySelector('input[name="page"]');
                        if (pageInput) {
                            pageInput.value = '1';
                        }
                        filterForm.submit();
                    }, 1000);
                });
            }
        });
    }
})