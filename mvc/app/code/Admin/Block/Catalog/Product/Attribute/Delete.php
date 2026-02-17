<?php

class Admin_Block_Catalog_Product_Attribute_Delete extends Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate("Admin/View/Catalog/Product/Attribute/delete.phtml");
    }

    public function _construct(){}
}