<?php
namespace core\abstracts;

use core\classes\database;
use core\classes\view;

abstract class controller
{

    private $params = [];

    public $route = [
        "controller" => "",
        "method" => ""
    ];

    /**
     *
     * @var view
     */
    protected $view;

    /**
     *
     * @var database
     */
    protected $db;

    public function __construct()
    {
        $this->view = new view();
        $this->db = $GLOBALS["db"];
    }

    public final function getParams()
    {
        return $this->params;
    }

    protected final function setParams($index, $value)
    {
        $this->params[$index] = $value;
    }

    public function init($route)
    {
        $this->route = $route;
    }

    public function __destruct()
    {
        if ($this->view->isDisabled()) {
            exit();
        }
        $this->view->render($this->route["controller"] . "::" . $this->route["method"], $this->getParams());
    }

    public function debugger()
    {
        $arr = func_get_args();

        echo '<pre>';
        foreach ($arr as $prop) {
            var_dump($prop);
        }
        echo '</pre>';
        $this->view->isDisabled();
        die();
    }

    protected function redirect($location, $code = 302)
    {
        $this->view->toggleRender();
        header("Location: " . $location, null, $code);
        exit();
    }
}
