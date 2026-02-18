<?php

class Admin_Block_Catalog_Category_New extends Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate("Admin/View/Catalog/Category/new.phtml");
    }

    public function _construct(){}
}