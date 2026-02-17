<?php

class Page_Controllers_Index
{
    public function indexAction()
    {

        $root = Sdp::getBlock('page/root');
        $home = Sdp::getBlock('page/home');

        $root->getChild('content')->addChild("home", $home);
        $root->toHtml();
    }
}