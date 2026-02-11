<?php
namespace App\View;

class View_Dashboard
{
    public function toHtml($template, $data = [])
    {
        extract($data);
        ob_start();
        $pageTitle = $data['pageTitle'] ?? 'EasyCart';

        if ($template === 'index') {
            $extraStyles = $data['extraStyles'] ?? [];
            $extraScripts = $data['extraScripts'] ?? [];
            $currentPage = $data['currentPage'] ?? 'dashboard';
            require __DIR__ . '/../../src/Views/dashboard.view.php';
        }

        return ob_get_clean();
    }
}
