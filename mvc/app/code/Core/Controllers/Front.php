<?php
class Core_Controllers_Front
{
    protected $_request;


    public function run()
    {
        // $request = new Core_Model_Request();
        $request = Sdp::getModel("core/request");

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
