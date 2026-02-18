<?php

class Admin_Controllers_Customer extends Core_Controllers_Admin
{
    public function newAction()
    {
        $root = Sdp::getBlock('page/root');
        $new = Sdp::getBlock("admin/customer_New");
        $root->getChild("content")->addChild("new", $new);   
        $root->toHtml();
    }

    public function editAction()
    {
        $root = Sdp::getBlock('page/root');
        $edit = Sdp::getBlock("admin/customer_Edit");
        $root->getChild("content")->addChild("edit", $edit);   
        $root->toHtml();
    }

    public function deleteAction()
    {
        $root = Sdp::getBlock('page/root');
        $delete = Sdp::getBlock("admin/customer_Delete");
        $root->getChild("content")->addChild("delete", $delete);   
        $root->toHtml();
    }

    public function listAction()
    {
        $root = Sdp::getBlock('page/root');
        $list = Sdp::getBlock("admin/customer_List");
        $root->getChild("content")->addChild("list", $list);   
        $root->toHtml();
    }

    // public function viewAction()
    // {
    //     $root = Sdp::getBlock("page/root");
    //     $view = Sdp::getBlock("catalog/product_View");
    //     $root->getChild("content")->addChild("view", $view);   
    //     $root->toHtml();
    // }
}