<?php

class Catalog_Block_Product_View_Specs extends Catalog_Block_Product_View
{
    public function _construct(){}
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate("Catalog/View/Product/View/specs.phtml");
    }
}
