<?php
namespace core\classes;

class session
{

    private static function fixObject(&$object)
    {
        if (! is_object($object) && gettype($object) == "object") {
            return $object = unserialize(serialize($object));
        }
        return $object;
    }

    public static function check($key)
    {
        if (is_array($key)) {
            $set = true;
            foreach ($key as $k) {
                if (! self::check($k)) {
                    $set = false;
                }
            }
            return $set;
        } else {
            return isset($_SESSION[$key]);
        }
    }

    public static function get($key, $remove = false)
    {
        if (self::check($key)) {
            $value = $_SESSION[$key];
            if ($remove) {
                self::kill($key);
            }
            self::fixObject($value);
            return $value;
        }
        return null;
    }

    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public static function kill($key)
    {
        if (self::check($key)) {
            unset($_SESSION[$key]);
        }
    }
}

