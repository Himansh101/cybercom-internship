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
        // $request = Sdp::getModel("core/request");
        // $base = $request->getBaseUrl();
        $root         = Sdp::getBlock("page/root");
        $view         = Sdp::getBlock("catalog/product_View");
        // $media        = Sdp::getBlock("catalog/product_View_Media");
        // $info         = Sdp::getBlock("catalog/product_View_Info");
        // $actions      = Sdp::getBlock("catalog/product_View_Actions");
        // $specs        = Sdp::getBlock("catalog/product_View_Specs");
        // $description  = Sdp::getBlock("catalog/product_View_Description");

        // // Add sub-parts
        // $view->addChild("media", $media);
        // $view->addChild("info", $info);
        // $view->addChild("actions", $actions);
        // $view->addChild("specs", $specs);
        // $view->addChild("description", $description);
        $product = Sdp::getModel('catalog/product');
        // echo "<pre>";
        $product->load(1);


        $root->getChild("content")->addChild("view", $view);
        $root->getChild("head")->addJs( "./../../js/catalog/product.js");
        $root->getChild("head")->addCss("./../../css/catalog/product.css");
        $root->toHtml();

        
        // print_r($product);
        // print_r($product->getUrl());
    }
}