<?php
namespace app\controllers;

use core\interfaces\controllerInterface;
use core\classes\session;

class main extends base implements controllerInterface
{

    public function index()
    {
        $this->view->addInlineCss("assets/css/common/index.css");
        if (session::check("user")) {
            $this->redirect("/home");
        }
    }
}
