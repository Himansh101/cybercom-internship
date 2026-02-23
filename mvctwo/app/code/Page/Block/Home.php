<?php

class Page_Block_Home extends Core_Block_Template
{
    public function __construct()
    {        parent::__construct();

        $this->setTemplate("page/view/home.phtml");
    }

    public function _construct()
    {
        $categories = Sdp::getBlock("page/home_categories");
        $hero = Sdp::getBlock("page/home_hero");
        $featured = Sdp::getBlock("page/home_featured");

        $this->addChild("categories", $categories);
        $this->addChild("hero", $hero);
        $this->addChild("featured", $featured);
    }
}