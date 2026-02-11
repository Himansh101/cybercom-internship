<?php
namespace App\View;

class View_Home extends BaseView
{
    public function toHtml($template, $data = [])
    {
        if ($template === 'index') {
            return $this->render('index', $data);
        }

        return '';
    }
}
