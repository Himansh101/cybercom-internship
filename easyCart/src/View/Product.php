<?php
namespace App\View;

class Product
{
    protected $layout = 'layout.php'; // Default layout? Or logic to include header/footer?

    public function toHtml($template, $data = [])
    {
        extract($data);
        ob_start();

        // We need to point to the existing view files.
        // Existing views are in `src/Views/`.
        // They might assume variables exist.
        // `plp.view.php` uses `$paginatedProducts`, `$categories`, etc.
        // I need to map my data to those expected variable names OR update the view files.
        // "Existing logic must be moved, not rewritten".
        // But I can update the view to use `$products` instead of `$paginatedProducts` if I pass it.
        // Let's explicitly map them in the Controller or here.

        // Mapping for PLP
        if ($template === 'plp') {
            // Data is already prepared by Controller
            $paginatedProducts = $data['paginatedProducts'] ?? [];
            $totalVisible = $data['totalVisible'] ?? 0;
            $pageNumber = $data['pageNumber'] ?? 1;
            $totalPages = $data['totalPages'] ?? 1;
            $startItem = $data['startItem'] ?? 0;
            $endItem = $data['endItem'] ?? 0;

            $searchQuery = $data['searchQuery'] ?? '';
            $selectedCats = $data['selectedCats'] ?? [];
            $selectedBrands = $data['selectedBrands'] ?? [];
            $selectedStock = $data['selectedStock'] ?? [];
            $minPrice = $data['minPrice'] ?? '';
            $maxPrice = $data['maxPrice'] ?? '';
            $sortBy = $data['sortBy'] ?? 'newest';

            // Re-map for view compatibility
            // The view file `src/Views/plp.view.php` can be included.
            require __DIR__ . '/../../src/Views/plp.view.php';
        } elseif ($template === 'pdp') {
            // Controller now passes exact keys needed by view: 
            // product, productId, categoryName, brandName, currentQtyInCart, pageTitle, etc.
            // extract($data) at top handles most of it.

            // Explicit check if we missed anything or need re-mapping?
            // Nope, Controller keys now match View variables:
            // $productId, $categoryName, $brandName, $currentQtyInCart.

            require __DIR__ . '/../../src/Views/pdp.view.php';
        }

        return ob_get_clean();
    }

    public function renderJson($data)
    {
        // Logic to render JSON for AJAX (mirroring the logic in plp.view.php but centrally)
        // Actually `src/Views/plp.view.php` has a block for AJAX:
        // `if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])...`
        // Since I'm refactoring, I should probably use `toHtml` and the view file's logic will handle it?
        // But the View class says "No business logic".
        // The View File `plp.view.php` has logic to detect AJAX and `json_encode`.
        // I should probably extract that.

        // For now, I will let `toHtml` include the file, which naturally handles the JSON output if the headers are set?
        // Wait, `plp.view.php` starts with `if (detect ajax) { echo json; exit; }`.
        // If I include it inside `ob_start()`, it will capture the JSON string.
        // Then `toHtml` returns it.
        // The Controller then echoes it.
        // This works.
        return $this->toHtml('plp', $data);
    }
}
