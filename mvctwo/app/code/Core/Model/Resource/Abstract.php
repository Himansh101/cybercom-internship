<?php

class Core_Model_Resource_Abstract
{
    protected $_tableName = null;
    protected $_primaryKey = null;

    protected function _init($tableName, $primaryKey)
    {
        $this->_tableName = $tableName;
        $this->_primaryKey = $primaryKey;

    }

    public function getTableName(){
        return $this->_tableName;
    }

    public function getPrimaryKey(){
        return $this->_primaryKey;
    }

    public function load($model, $value, $field)
    {
        $mysql = Sdp::getModel('core/connection_Mysql');
        $field = (is_null($field)) ? $this->getPrimaryKey() : $field;
        $query = "SELECT * FROM {$this->getTableName()} WHERE {$field} = {$value}";
        $data = $mysql->fetchOne($query);
        return $data;
    }



}