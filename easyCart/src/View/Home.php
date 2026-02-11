<?php
namespace App\View;

class Home
{
    public function toHtml($template, $data = [])
    {
        extract($data);
        ob_start();
        $pageTitle = $data['pageTitle'] ?? 'EasyCart';

        if ($template === 'index') {
            require __DIR__ . '/../../src/Views/index.view.php';
        }

        return ob_get_clean();
    }
}
