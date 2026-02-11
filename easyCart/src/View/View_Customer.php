<?php
namespace App\View;

class View_Customer extends BaseView
{
    public function toHtml($template, $data = [])
    {
        if ($template === 'login') {
            return $this->render('login', $data);
        } elseif ($template === 'signup') {
            return $this->render('signup', $data);
        }

        return '';
    }
}
