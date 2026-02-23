<?php

class Page_Block_Header extends Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate("Page/View/header.phtml");
    }

    public function _construct(){

        $icon   = Sdp::getBlock("page/header_icon");
        $menu   = Sdp::getBlock("page/header_menu");
        $search = Sdp::getBlock("page/header_search");

        $this->addChild("icon",   $icon);
        $this->addChild("menu",   $menu);
        $this->addChild("search", $search);
    }
}