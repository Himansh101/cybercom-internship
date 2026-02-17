<?php

class Customer_Block_Account_Index extends Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate("Customer/View/Account/index.phtml");
    }

    public function _construct(){}
}
