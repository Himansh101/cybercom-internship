<?php
class Core_Controllers_Front{
    protected $_request;

    function __construct()
    {
        $admin = new Core_Controllers_Admin();
        echo "<pre>";
        print_r($admin);
        echo "</pre>";
    }
}

?>