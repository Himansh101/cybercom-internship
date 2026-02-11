<?php
namespace App\View;

class View_Checkout extends BaseView
{
    public function toHtml($template, $data = [])
    {
        if ($template === 'index') {
            return $this->render('checkout', $data);
        }

        return '';
    }
}
