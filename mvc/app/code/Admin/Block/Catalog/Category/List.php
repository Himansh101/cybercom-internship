<?php

class Admin_Block_Catalog_Category_List extends Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate("Admin/View/Catalog/Category/list.phtml");
    }

    public function _construct(){}
}