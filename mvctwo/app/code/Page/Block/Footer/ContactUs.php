<?php
class Page_Block_Footer_ContactUs extends Core_Block_Template
{
    public function __construct()
    {
        $this->setTemplate("Page/View/Footer/contactUs.phtml");
    }

    public function getAddress()
    {
        return "123 Tech Park, Innovation Street, Ahmedabad, Gujarat - 380001";
    }

    public function getPhone()
    {
        return "+91 98765 43210";
    }

    public function getEmail()
    {
        return "info@cybercom.com";
    }
}
