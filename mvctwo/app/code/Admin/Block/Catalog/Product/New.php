<?php

class Admin_Block_Catalog_Product_New extends Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate("Admin/View/Catalog/Product/new.phtml");
    }

    public function _construct(){}
}