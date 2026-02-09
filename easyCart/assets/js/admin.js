document.addEventListener('DOMContentLoaded', () => {
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('csv-file');
    const fileName = document.getElementById('file-name');
    const importBtn = document.getElementById('import-btn');
    const importForm = document.getElementById('import-form');
    const resultsDiv = document.getElementById('import-results');

    if (!dropZone || !fileInput) return;

    // Drag and drop handlers
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.add('dragover'));
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.remove('dragover'));
    });

    dropZone.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        if (files.length > 0 && files[0].name.endsWith('.csv')) {
            fileInput.files = files;
            handleFileSelect(files[0]);
        }
    });

    dropZone.addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
            handleFileSelect(fileInput.files[0]);
        }
    });

    function handleFileSelect(file) {
        fileName.textContent = `Selected: ${file.name}`;
        importBtn.disabled = false;
    }

    // Import form submission
    importForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!fileInput.files.length) return;

        importBtn.disabled = true;
        importBtn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Importing...';
        resultsDiv.classList.add('hidden');

        const formData = new FormData(importForm);

        try {
            const response = await fetch('src/handlers/admin.handler', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.status === 'success') {
                resultsDiv.innerHTML = `
                    <h4><i class="ri-check-double-line"></i> Import Complete</h4>
                    <div class="result-row">
                        <span>Products Inserted</span>
                        <span class="success"><strong>${data.inserted}</strong></span>
                    </div>
                    <div class="result-row">
                        <span>Duplicates Skipped</span>
                        <span class="warning"><strong>${data.skipped}</strong></span>
                    </div>
                    ${data.errors.length > 0 ? `
                    <div class="result-row">
                        <span>Errors</span>
                        <span class="error"><strong>${data.errors.length}</strong></span>
                    </div>
                    <ul style="color: #ef4444; font-size: 0.85rem; margin-top: 10px; padding-left: 20px;">
                        ${data.errors.map(e => `<li>${e}</li>`).join('')}
                    </ul>
                    ` : ''}
                `;
                resultsDiv.classList.remove('hidden');

                // Reset form
                fileInput.value = '';
                fileName.textContent = '';

                // Show toast
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: data.inserted > 0 ? 'success' : 'warning',
                        title: data.inserted > 0 ? 'Import Successful' : 'No Products Added',
                        text: `${data.inserted} products imported, ${data.skipped} skipped.`,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 4000,
                        timerProgressBar: true
                    });
                }
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            console.error('Import error:', error);
            if (typeof Swal !== 'undefined') {
                Swal.fire('Import Failed', error.message || 'An error occurred.', 'error');
            }
        } finally {
            importBtn.disabled = false;
            importBtn.innerHTML = '<i class="ri-upload-2-line"></i> Import Products';
        }
    });

    // Export handling
    const exportBtn = document.getElementById('export-btn');
    if (exportBtn) {
        exportBtn.addEventListener('click', async () => {
            exportBtn.disabled = true;
            const originalHTML = exportBtn.innerHTML;
            exportBtn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Exporting...';

            try {
                const response = await fetch('src/handlers/admin.handler?action=export_products');

                if (!response.ok) throw new Error('Export failed');

                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                // Set filename from header or use default
                const contentDisposition = response.headers.get('Content-Disposition');
                let fileName = 'products_export.csv';
                if (contentDisposition && contentDisposition.indexOf('filename=') !== -1) {
                    fileName = contentDisposition.split('filename=')[1].replace(/"/g, '');
                }
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Export Successful',
                        text: 'Your CSV file has been downloaded.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                }
            } catch (error) {
                console.error('Export error:', error);
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Export Failed', 'An error occurred while generating the CSV.', 'error');
                }
            } finally {
                exportBtn.disabled = false;
                exportBtn.innerHTML = originalHTML;
            }
        });
    }
});
