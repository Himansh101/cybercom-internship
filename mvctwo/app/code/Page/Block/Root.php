<?php

class Page_Block_Root extends Core_Block_Template{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate("Page/View/root.phtml");
    }

    public function _construct()
    {
        $head = Sdp::getBlock("page/head");
        $header = Sdp::getBlock("page/header");
        
        $content = Sdp::getBlock("page/content");
        $footer = Sdp::getBlock("page/footer");

        $this->addChild("head", $head);
        $this->addChild("header", $header);
        
        $this->addChild("content", $content);
        $this->addChild("footer", $footer);
    }
}