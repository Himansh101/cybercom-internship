<?php

class Page_Block_Menu extends Core_Block_Template
{
    public function __construct()
    {
        $this->setTemplate("Page/View/menu.phtml");
    }

    public function getMenuArray(){
        return [
            "url1" => "Category 1",
            "url2" => "Category 2",
            "url3" => "Category 3",
        ];
    }
}