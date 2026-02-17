<?php

spl_autoload_register(function($class){

    $base = __DIR__.'/';
    $file = str_replace('_', '/', $class);
    $file = sprintf('%s.php', $base . $file);
    require_once $file;

});