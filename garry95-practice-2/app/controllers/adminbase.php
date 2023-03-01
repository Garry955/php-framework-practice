<?php
namespace app\controllers;

use core\abstracts\controller;
use core\classes\session;

abstract class adminbase extends controller
{

    public final function init($route)
    {
        parent::init($route);
        if (! session::check("admin") && ! ($this->route["controller"] == "admin" && in_array($this->route["method"], [
            "index",
            "logout"
        ]))) {
            $controller = new admin();
            $controller->route = [
                "controller" => "admin",
                "method" => "index"
            ];
            $controller->index();
            exit();
        }
        $this->setParams("admin", session::get("admin"));
    }

    public function __destruct()
    {
        if ($this->view->isDisabled()) {
            exit();
        }
        $this->view->addCss("/assets/css/generals/reset.css")
            ->addCss("/assets/css/generals/generals.css")
            ->addCss("/assets/css/generals/error.css")
            ->addCss("/assets/css/parts/fonts.css")
            ->addCss("/assets/css/parts/fa.min.css")
            ->addInlineCss("assets/css/parts/form.css")
            ->addInlineCss("assets/css/parts/content.css")
            ->addInlineCss("assets/css/admin/base/admin.css")
            ->addInlineCss("assets/css/admin/base/header.css")
            ->addInlineCss("assets/css/admin/base/sidebar.css")
            ->addJs("assets/js/admin/core.js")
            ->addJs("https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js");


        $this->view->render("admin::template::header", $this->getParams());

        if ($_SERVER["REQUEST_URI"] != "/admin" && session::check("admin")) {
            $this->view->render("admin::template::sidebar", $this->getParams());
        }
        $this->view->render("admin::" . $this->route["controller"] . "::" . $this->route["method"], $this->getParams());

        $this->setParams("message", session::get("message", true));
        $this->view->render("template::message", $this->getParams());
        $this->view->render("admin::template::footer", $this->getParams());
        exit();
    }
}

