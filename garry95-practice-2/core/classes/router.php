<?php
namespace core\classes;

use core\autoload;

class router
{

    private $routes;

    function __construct()
    {
        $this->routes = $GLOBALS["config"]["routes"];
        $route = $this->findRoute();
        $controller = autoload::load($route["controller"], true);
        if ($controller) {
            $controller->init($route);
            if (method_exists($controller, $route["method"])) {
                $controller->$route["method"]();
            } else {
                error::show(404);
            }
        } else {
            error::show(404);
        }
    }

    private function routePart($route)
    {
        if (is_array($route)) {
            $route = $route["url"];
        }
        $parts = explode("/", $route);
        return $parts;
    }

    static function uri($part)
    {
        $parts = explode("/", parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));
        if ($parts[1] == $GLOBALS["config"]["path"]["index"]) {
            $part ++;
        }
        return (isset($parts[$part])) ? $parts[$part] : "";
    }

    private function findRoute()
    {
        foreach ($this->routes as $route) {
            $parts = $this->routePart($route);
            $allMatch = true;
            foreach ($parts as $key => $value) {
                if ($value != "*") {
                    if (router::uri($key) != $value) {
                        $allMatch = false;
                    }
                }
            }
            if ($allMatch) {
                return $route;
            }
        }
        $uri_1 = router::uri(1);
        $uri_2 = router::uri(2);

        if ($uri_1 == "") {
            $uri_1 = $GLOBALS["config"]["defaults"]["controller"];
        }
        if ($uri_2 == "") {
            $uri_2 = $GLOBALS["config"]["defaults"]["method"];
        }
        $route = [
            "controller" => $uri_1,
            "method" => $uri_2
        ];
        return $route;
    }
}
