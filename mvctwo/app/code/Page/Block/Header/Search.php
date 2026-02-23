<?php
class Page_Block_Header_Search extends Core_Block_Template
{
    public function __construct()
    {
        $this->setTemplate("Page/View/Header/search.phtml");
    }

    public function getPlaceholder()
    {
        return "Search products...";
    }
}
