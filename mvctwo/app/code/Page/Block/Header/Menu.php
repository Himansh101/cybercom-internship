<?php
class Page_Block_Header_Menu extends Core_Block_Template
{
    public function __construct()
    {
        $this->setTemplate("Page/View/Header/menu.phtml");
    }

    public function getNavLinks()
    {
        $homeurl = Sdp::getModel("Page/Home")->getBaseUrl();
        $productUrl = "catalog/product/list";
        return [
            $homeurl => "Home",
            $productUrl => "Products",
            "/cart" => "Cart",
            "/profile" => "Profile",
        ];
    }
}
