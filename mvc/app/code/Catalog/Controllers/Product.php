<?php

class Catalog_Controllers_Product extends Core_Controllers_Front
{
    public function listAction()
    {
        $root = Sdp::getBlock('page/root');
        $list = Sdp::getBlock("catalog/product_List");
        $root->getChild("content")->addChild("list", $list);   
        $root->toHtml();
    }

    public function viewAction()
    {
        $root = Sdp::getBlock("page/root");
        $view = Sdp::getBlock("catalog/product_View");
        $root->getChild("content")->addChild("view", $view); 
        $root->getChild("head")->addJs("js/catalog/product.js");
        $root->getChild("head")->addCss("css/catalog/product.css");
        $root->toHtml();
    }

    
}