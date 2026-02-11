<?php
namespace App\View;

class View_Profile
{
    public function toHtml($template, $data = [])
    {
        extract($data);
        ob_start();
        $pageTitle = $data['pageTitle'] ?? 'EasyCart';

        if ($template === 'index') {
            $extraStyles = $data['extraStyles'] ?? [];
            $extraScripts = $data['extraScripts'] ?? [];
            $currentPage = $data['currentPage'] ?? 'profile';

            require __DIR__ . '/../../src/Views/profile.view.php';
        }

        return ob_get_clean();
    }
}
