<?php

class Page_Block_Header extends Core_Block_Template{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate("Page/View/header.phtml");
    }

    public function _construct(){
        $menu = Sdp::getBlock("page/menu");
        $this->addChild("menu", $menu);
    }
}