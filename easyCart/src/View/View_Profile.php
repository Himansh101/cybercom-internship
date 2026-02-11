<?php
namespace App\View;

class View_Profile extends BaseView
{
    public function toHtml($template, $data = [])
    {
        if ($template === 'index') {
            return $this->render('profile', $data);
        }

        return '';
    }
}
