<?php

class Customer_Block_Account_Address extends Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate("Customer/View/Account/address.phtml");
    }

    public function _construct(){}
}