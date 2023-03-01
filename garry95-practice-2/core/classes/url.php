<?php
namespace core\classes;

class url
{

    public static function part($number)
    {
        $uri = explode("?", $_SERVER["REQUEST_URI"]);
        $parts = explode("/", $uri[0]);
        return (isset($parts[$number]) ? $parts[$number] : false);
    }

    public static function post($key)
    {
        return (isset($_POST[$key])) ? $_POST[$key] : false;
    }

    public static function get($key)
    {
        return (isset($_GET[$key])) ? urldecode($_GET[$key]) : false;
    }

    public static function request($key)
    {
        if (url::get($key)) {
            return url::get($key);
        } else if (url::post($key)) {
            return url::post($key);
        } else {
            return false;
        }
    }

    public static function build($url, $params)
    {
        if (strpos($url, "//") === false) {
            $prefix = "//" . $GLOBALS["config"]["domain"];
        } else {
            $prefix = "";
        }
        $append = "";
        foreach ($params as $key => $params) {
            $append .= ($append == "") ? "?" : "&";
            $append .= urlencode($key) . "=" . urlencode($params);
        }
        return $prefix . $append;
    }

    public static function simple($url)
    {
        if (strpos($url, "//") === false) {
            $prefix = "//" . $GLOBALS["config"]["domain"];
        } else {
            $prefix = "";
        }
        return $prefix;
    }
}

