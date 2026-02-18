<?php

class Admin_Controllers_Catalog_Category extends Core_Controllers_Admin
{
    public function newAction()
    {
        $root = Sdp::getBlock('page/root');
        $new = Sdp::getBlock("admin/catalog_Category_New");
        $root->getChild("content")->addChild("new", $new);   
        $root->toHtml();
    }

    public function listAction()
    {
        $root = Sdp::getBlock('page/root');
        $list = Sdp::getBlock("admin/catalog_Category_List");
        $root->getChild("content")->addChild("list", $list);   
        $root->toHtml();
    }

    public function editAction()
    {
        $root = Sdp::getBlock('page/root');
        $edit = Sdp::getBlock("admin/catalog_Category_Edit");
        $root->getChild("content")->addChild("edit", $edit);   
        $root->toHtml();
    }

    public function deleteAction()
    {
        $root = Sdp::getBlock('page/root');
        $delete = Sdp::getBlock("admin/catalog_Category_Delete");
        $root->getChild("content")->addChild("delete", $delete);   
        $root->toHtml();
    }

}