<?php

class Catalog_Block_Product_List extends Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate("Catalog/View/Product/list.phtml");
    }

    public function _construct(){
        
    }
}