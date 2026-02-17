<?php

class Customer_Controllers_Account extends Core_Controllers_Front
{
    public function indexAction()
    {
        $root = Sdp::getBlock("page/root");
        $index = Sdp::getBlock("customer/account_Index");
        $root->getChild("content")->addChild("index", $index);   
        $root->toHtml();
    }

    public function addressAction()
    {
        $root = Sdp::getBlock("page/root");
        $address = Sdp::getBlock("customer/account_Address");
        $root->getChild("content")->addChild("address", $address);   
        $root->toHtml();
    }

    public function editAction()
    {
        $root = Sdp::getBlock("page/root");
        $edit = Sdp::getBlock("customer/account_Edit");
        $root->getChild("content")->addChild("edit", $edit);   
        $root->toHtml();
    }

    public function saveAction()
    {
        $root = Sdp::getBlock("page/root");
        $save = Sdp::getBlock("customer/account_Save");
        $root->getChild("content")->addChild("save", $save);   
        $root->toHtml();
    }
}