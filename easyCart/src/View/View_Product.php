<?php
namespace App\View;

class View_Product extends BaseView
{
    protected $layout = 'layout.php'; // Default layout? Or logic to include header/footer?

    public function toHtml($template, $data = [])
    {
        if ($template === 'plp') {
            return $this->render('plp', $data);
        } elseif ($template === 'pdp') {
            return $this->render('pdp', $data);
        }

        return '';
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
