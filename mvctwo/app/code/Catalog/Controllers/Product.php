<?php

class Catalog_Controllers_Product{
    public function listAction(){
        $root = Sdp::getBlock('page/root');
        $root->toHtml();
    }

    public function viewAction(){
        $root = Sdp::getBlock("page/root");
        $view = Sdp::getBlock("catalog/product_View");
        $root->getChild("content")->addChild("view", $view);   

        $root->toHtml();
    }
}