<?php

class Catalog_Block_Product_View_Media extends Catalog_Block_Product_View
{
    public function _construct(){}
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate("Catalog/View/Product/View/media.phtml");
    }
}
