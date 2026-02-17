<?php

class Catalog_Block_Product_View extends Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate("Catalog/View/Product/view.phtml");
    }

    public function _construct(){
        
    }
}