<?php

class Catalog_Block_Product_View extends Core_Block_Template
{
    protected $request;
    protected $base;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate("Catalog/View/Product/view.phtml");
        $this->request = Sdp::getModel("core/request");
        $this->base = $this->request->getBaseUrl();
    }

    public function _construct()
    {
        $media        = Sdp::getBlock("catalog/product_View_Media");
        $info         = Sdp::getBlock("catalog/product_View_Info");
        $actions      = Sdp::getBlock("catalog/product_View_Actions");
        $specs        = Sdp::getBlock("catalog/product_View_Specs");
        $description  = Sdp::getBlock("catalog/product_View_Description");

        $this->addChild("media", $media);
        $this->addChild("info", $info);
        $this->addChild("actions", $actions);
        $this->addChild("specs", $specs);
        $this->addChild("description", $description);
    }

    public function getProduct()
    {
        $product = Sdp::getModel("catalog/product");
        $id = $this->request->getParams();
        $productId = isset($id['id']) ? (int)$id['id'] : 1;

        // $productId = isset($id['id'])
        //         ? (int)$id['id']
        //         : 1;

        $product->load($productId);
        return $product;
    }
}
?>