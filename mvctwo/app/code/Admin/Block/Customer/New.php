<?php

class Admin_Block_Customer_New extends Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate("Admin/View/Customer/new.phtml");
    }

    public function _construct(){}
}