<?php
namespace core\classes;

use core\autoload;

class error
{

    static function show($type)
    {
        $class = autoload::load("error", true);
        $method = "error" . $type;
        $class->route["controller"] = "error";
        $class->route["method"] = $method;
        $class->$method();
    }
}
