<?php
namespace core;

use core\classes\router;
use core\classes\database;

class autoload
{

    public function __construct()
    {
        if (file_exists("vendor" . DIRECTORY_SEPARATOR . "autoload.php")) {
            require_once "vendor" . DIRECTORY_SEPARATOR . "autoload.php";
        }
        spl_autoload_register([
            $this,
            "load"
        ]);
    }

    public function init()
    {
        $GLOBALS["db"] = new database();
        new router();
    }

    public static function load($className, $isInstantiable = false)
    {
        if (strpos($className, "\\") === false) {
            $className = "app\\controllers\\" . $className;
        }
        if (! class_exists($className, false)) {
            $filename = realpath(str_replace("\\", DIRECTORY_SEPARATOR, trim($className, "\\")) . ".php");
            if ($filename) {
                require_once $filename;
                if ($isInstantiable) {
                    $ref = new \ReflectionClass($className);
                    if ($ref->isInstantiable()) {
                        return new $className();
                    }
                }
            }
        } else {
            if ($isInstantiable) {
                return new $className();
            }
        }
        return false;
    }
}