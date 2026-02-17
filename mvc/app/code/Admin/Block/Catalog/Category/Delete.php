<?php

class Admin_Block_Catalog_Category_Delete extends Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate("Admin/View/Catalog/Category/delete.phtml");
    }

    public function _construct(){}
}