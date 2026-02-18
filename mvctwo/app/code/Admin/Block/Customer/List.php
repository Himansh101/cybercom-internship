<?php

class Admin_Block_Customer_List extends Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate("Admin/View/Customer/list.phtml");
    }

    public function _construct(){}

}