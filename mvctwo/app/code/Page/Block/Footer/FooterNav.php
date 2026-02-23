<?php
class Page_Block_Footer_FooterNav extends Core_Block_Template
{
    public function __construct()
    {
        $this->setTemplate("Page/View/Footer/footerNav.phtml");
    }

    public function getNavLinks()
    {
        return [
            "/"        => "Home",
            "/product" => "Product",
            "/profile" => "Profile",
        ];
    }
}
