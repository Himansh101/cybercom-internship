<?php
namespace App\View;

class View_Cart extends BaseView
{
    public function toHtml($template, $data = [])
    {
        if ($template === 'index') {
            return $this->render('cart', $data);
        }

        return '';
    }
}
