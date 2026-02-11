<?php
namespace App\View;

class View_Order extends BaseView
{
    public function toHtml($template, $data = [])
    {
        if ($template === 'index') {
            return $this->render('orders', $data);
        }

        return '';
    }
}
