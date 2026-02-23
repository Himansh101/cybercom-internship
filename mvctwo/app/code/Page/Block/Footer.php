<?php

class Page_Block_Footer extends Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate("Page/View/footer.phtml");
    }

    public function _construct()
    {
        $companyInfo = Sdp::getBlock("page/footer_companyInfo");
        $footerNav   = Sdp::getBlock("page/footer_footerNav");
        $contactUs   = Sdp::getBlock("page/footer_contactUs");

        $this->addChild("company_info", $companyInfo);
        $this->addChild("footer_nav",   $footerNav);
        $this->addChild("contact_us",   $contactUs);
    }
}