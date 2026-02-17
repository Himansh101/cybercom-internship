<?php

class Customer_Block_Account_Edit extends Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate("Customer/View/Account/edit.phtml");
    }
    
    public function _construct(){}

}