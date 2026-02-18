<?php

class Core_Block_Template
{
    protected $_child = [];
    protected $_parent = null;
    protected $_template = null;


    public function __construct()
    {
        $this->_construct();
    }

    public function setTemplate($template)
    {
        $this->_template = $template;
    }

    public function toHtml()
    {
        include getcwd() . "/app/code/" . $this->_template;
    }

    public function addChild($name, $block)
    {
        $this->_child[$name] = $block;
    }

    public function getChild($name)
    {
        if (isset($this->_child[$name])) {
            return $this->_child[$name];
        }
    }

    public function getChildHtml($name = "")
    {
        if(isset($this->_child[$name])){
            $this->_child[$name]->toHtml();
        }else{
            if(count($this->_child)){
                foreach($this->_child as $child){
                    $child->toHtml();
                }
            }
        }
    }

}