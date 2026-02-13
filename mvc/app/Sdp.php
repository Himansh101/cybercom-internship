<?php
class Sdp{
    static public function run(){
        $request = new Core_Model_Request();

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