<?php
namespace app\controllers;

use core\abstracts\controller;
use app\models\Menu;
use core\classes\session;

abstract class base extends controller
{

    public function __construct()
    {
        parent::__construct();

        $menus = Menu::find();

        $this->setParams("menus", $menus);
        $this->setParams("message", session::get("message", true));
    }

    public function __destruct()
    {
        if ($this->view->isDisabled()) {
            exit();
        }
        $this->view->addCss("/assets/css/generals/reset.css")
            ->addCss("/assets/css/generals/generals.css")
            ->addCss("/assets/css/generals/error.css")
            ->addInlineCss("assets/css/parts/header.css")
            ->addInlineCss("assets/css/parts/footer.css")
            ->addInlineCss("assets/css/parts/content.css")
            ->addCss("/assets/css/parts/form.css")
            ->addCss("/assets/css/parts/fonts.css")
            ->addCss("/assets/css/parts/fa.min.css")
            ->addJs("https://code.jquery.com/jquery-3.3.1.min.js")
            ->addJs("assets/js/common/core.js");

        $this->view->render("template::header", $this->getParams());

        parent::__destruct();

        $this->view->render("template::message", $this->getParams());
        $this->view->render("template::footer", $this->getParams());
        exit();
    }
}

