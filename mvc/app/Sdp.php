<?php

class Sdp{
    public static function run(){
        $front = new Core_Controllers_Front();
        $front->run();
    }

    public static function getModel($modelName){
        $model = array_map("ucfirst", explode("/", $modelName));
        $model = sprintf("%s_Model_%s", $model[0], $model[1]);
        $modelObj = new $model();
        return $modelObj;
    }

    public static function getBlock($blockName){
        $block = array_map("ucfirst", explode("/", $blockName));
        $block = sprintf("%s_Block_%s", $block[0], $block[1]);
        $blockObj = new $block();
        return $blockObj;
    }
}

?>