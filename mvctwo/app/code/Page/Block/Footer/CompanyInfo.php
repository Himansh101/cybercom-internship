<?php
class Page_Block_Footer_CompanyInfo extends Core_Block_Template
{
    public function __construct()
    {
        $this->setTemplate("Page/View/Footer/companyInfo.phtml");
    }

    public function getCompanyName()
    {
        return "MVC1 Store";
    }

    public function getCompanyDescription()
    {
        return "We are a forward-thinking technology company delivering innovative digital solutions to businesses worldwide. Our mission is to empower growth through cutting-edge technology.";
    }
}
