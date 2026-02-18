<?php

class Admin_Block_Catalog_Product_Attribute_List extends Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate("Admin/View/Catalog/Product/Attribute/list.phtml");
    }

    public function _construct(){}
}