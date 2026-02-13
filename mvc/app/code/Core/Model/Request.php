<?php
class Core_Model_Request
{
    protected $_module = "page";
    protected $_controllers = "index";
    protected $_action = "index";

    public function __construct(){
        $uri = $this->getRequestUri();
        $uri = str_replace($this->getBaseUrl(), "", $uri);
        $uri = explode("/", $uri);

        $this->_module = $uri[0];
        $this->_controllers = $uri[1];
        $this->_action = $uri[2];
        // echo "<pre>";
        // print_r($uri);
    }

    public function getRequestUri(){
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $fullUrl  = $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        return $fullUrl;

        // return $_SERVER['REQUEST_URI'];
    }

    public function getParams()
    {
        return $_REQUEST;
    }

    public function isPost()
    {
        return (isset($_POST)) ? true : false;
    }

    public function getQuery()
    {
        return $_GET;
    }

    public function getPost()
    {
        return $_POST;
    }

    public function getBaseUrl()
    {
        return "http://localhost/cybercom-internship/mvc/";
    }

    public function getModuleName(){
        return $this->_module;
    }

    public function getControllerName(){
        return $this->_controllers;
    }

    public function getActionName(){
        return $this->_action;
    }
}
