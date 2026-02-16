<?php

class Page_Block_Head extends Core_Block_Template
{
    public function __construct()
    {
        $this->setTemplate("Page/View/head.phtml");
    }
}