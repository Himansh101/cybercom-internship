<?php

class Core_Controllers_Front extends Core_Controllers_Front_Action
{
    protected $_request;

    public function run(){
        // $request = new Core_Model_Request();
        $request = $this->getRequest();

        $className = sprintf(
            "%s_Controllers_%s",
            ucfirst($request->getModuleName()),
            ucfirst($request->getControllerName())  
        );
        $action = $request->getActionName() . "Action";
        $classObj = new $className();
        $classObj->$action();
    }
}

?>