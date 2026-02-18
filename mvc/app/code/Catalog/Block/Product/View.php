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

    public function getProduct()
    {
        $product = Sdp::getModel("catalog/product");
        $product->addData(
            [
                "product_id" => 1,
                "name"=> "dell laptop 001",
                "url" => "dell-laptop-001" 
            ]
        );
        echo "<pre>";
        print_r($product);

        return $product;
    }
}
?>