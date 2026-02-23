<?php

class Page_Block_Head extends Core_Block_Template
{
    protected $request;
    protected $base;
    protected $_js = [];
    protected $_css = [];

    public function __construct()
    {
        $this->request = Sdp::getModel("core/request");
        $this->base = $this->request->getBaseUrl();
        $this->setTemplate("Page/View/head.phtml");
        $this->addJs($this->base."js/default.js")
            ->addJs($this->base."js/default1.js")
            ->addJs($this->base."js/home.js")
            ->addCss($this->base."css/header.css")
            ->addCss($this->base."css/footer.css")
            ->addCss($this->base."css/default1.css")
            ->addCss($this->base."css/home.css");
    }

    public function addJs($file)
    {
        $this->_js[] = $file;
        return $this;
    } 

    public function getJs()
    {
        return $this->_js;
    }
    
    public function addCss($file)
    {
        $this->_css[] = $file;
        return $this; 
    }
        
    public function getCss()
    {
        return $this->_css;
    }



}