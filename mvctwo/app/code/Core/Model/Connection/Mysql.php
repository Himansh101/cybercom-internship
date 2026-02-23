<?php

class Core_Model_Connection_Mysql
{
    protected $_connection = null;

    public function connect()
    {
        if (is_null($this->_connection)) {

            $this->_connection = new mysqli("localhost", "root", "", "mvc");
        }

        if ($this->_connection->connect_error) {
            die("Connection failed: " . $this->_connection->connect_error);
        }
    }

    public function __construct()
    {
        $this->connect();
    }

    public function fetchOne()
    {

        $sql = "SELECT * FROM catalog_product";
        $result = $this->_connection->query($sql);

        while ($row = $result->fetch_assoc()) {
            return $row;
        }
    }

    public function __destruct()
    {
        $this->_connection->close();
    }
}
