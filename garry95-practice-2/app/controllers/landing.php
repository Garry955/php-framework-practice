<?php
namespace app\controllers;

use app\controllers\base;
use core\interfaces\controllerInterface;
use core\classes\session;

class landing extends base implements controllerInterface
{

    public function index()
    {
        if (! session::check("landing")) {
            $this->redirect("/");
        }
        $this->setParams("name", session::get("name"));
        $this->setParams("email", session::get("email"));
    }
}

