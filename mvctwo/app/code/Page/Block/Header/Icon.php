<?php
class Page_Block_Header_Icon extends Core_Block_Template
{
    public function __construct()
    {
        $this->setTemplate("Page/View/Header/icon.phtml");
    }

    public function getSiteName()
    {
        return "MVC1 Store";
    }

    public function getSiteUrl()
    {
        return "/";
    }
}
