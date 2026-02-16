<?php
class Page_Controllers_Index
{
    public function indexAction()
    {
        // echo "<pre>";
        // print_r($head);

        // echo "<pre>";
        // print_r($header);
        
        $root = Sdp::getBlock("page/root");
        $home = Sdp::getBlock('page/home');
        $root->getChild('content')->addChild('home', $home);

        $root->toHtml();
        // echo "<pre>";
        // print_r($root);


    }
}
