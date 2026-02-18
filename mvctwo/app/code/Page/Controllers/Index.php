<?php

class Page_Controllers_Index extends Core_Controllers_Front
{
    public function indexAction()
    {

        $root = Sdp::getBlock('page/root');
        $home = Sdp::getBlock('page/home');

        $root->getChild('content')->addChild("home", $home);
        $root->toHtml();
    }
}