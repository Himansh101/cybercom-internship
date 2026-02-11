<?php
namespace App\View;

class View_Admin extends BaseView
{
    public function toHtml($template, $data = [])
    {
        if ($template === 'index') {
            return $this->render('admin', $data);
        }

        return '';
    }
}
