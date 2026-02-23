<?php

class Core_Model_Abstract{
    protected $_data = [];

    public function __set($key, $value)
    {
        $this->_data[$key] = $value;
        return $this;
    }

    public function __get($key)
    {
        return $this->_data[$key];
        
    }

    public function addData($data = [])
    {
        $this->_data = $data;
        return $this;
    }


    public function load($value, $field = null)
    {   
        $query = "SELECT * FROM catalog_product";
    }
    
}