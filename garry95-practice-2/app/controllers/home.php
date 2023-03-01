<?php
namespace app\controllers;

use core\interfaces\controllerInterface;
use core\classes\session;

class home extends base implements controllerInterface
{

    public function index()
    {
        if (! session::check("user")) {
            $this->redirect("/");
        } else {
            $this->setParams("user", session::get("user"));
        }
        $this->view->addInlineCss("assets/css/common/home.css");
    }
}
