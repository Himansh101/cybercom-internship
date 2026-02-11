<?php
namespace App\View;

class BaseView
{
    /**
     * @var array Persisted view data for partials
     */
    protected $viewData = [];

    /**
     * Render a template with data
     * 
     * @param string $templatePath Path relative to src/views/ (without .view.php)
     * @param array $data Data to be extracted for the view
     * @return string
     */
    public function render($templatePath, $data = [])
    {
        // Persist data for partials to inherit
        $this->viewData = $data;

        // Pull in standard global variables from init.php
        global $isLoggedIn, $cartQuantity, $user, $userId, $cartId;

        // Extract data to make it available as variables in the template
        if (!empty($data)) {
            extract($data);
        }

        ob_start();

        // Use absolute path to ensure accuracy
        $viewFile = __DIR__ . "/../../src/views/{$templatePath}.view.php";

        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            echo "<!-- View file not found: {$templatePath} -->";
        }

        return ob_get_clean();
    }

    /**
     * Include a partial (e.g., header/footer) inside a template
     * 
     * @param string $partialName Name of the partial in src/partials/
     * @param array $data Data context override
     */
    public function partial($partialName, $data = [])
    {
        // Pull in standard global variables from init.php
        global $isLoggedIn, $cartQuantity, $user, $userId, $cartId;

        // Merge instance view data with local data override
        $mergedData = array_merge($this->viewData, $data);

        if (!empty($mergedData)) {
            extract($mergedData);
        }

        $partialFile = __DIR__ . "/../../src/partials/{$partialName}.view.php";

        if (file_exists($partialFile)) {
            require $partialFile;
        } else {
            echo "<!-- Partial not found: {$partialName} -->";
        }
    }
}