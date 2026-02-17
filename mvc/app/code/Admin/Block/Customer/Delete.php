<?php

class Admin_Block_Customer_Delete extends Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate("Admin/View/Customer/delete.phtml");
    }

    public function _construct(){}

}