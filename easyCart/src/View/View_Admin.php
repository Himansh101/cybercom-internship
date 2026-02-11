<?php
namespace App\View;

class View_Admin
{
    public function toHtml($template, $data = [])
    {
        extract($data);
        ob_start();
        $pageTitle = $data['pageTitle'] ?? 'EasyCart | Admin';

        if ($template === 'index') {
            $extraStyles = $data['extraStyles'] ?? [];
            $extraScripts = $data['extraScripts'] ?? [];
            $currentPage = $data['currentPage'] ?? 'admin';
            require __DIR__ . '/../../src/Views/admin.view.php';
        }

        return ob_get_clean();
    }
}
