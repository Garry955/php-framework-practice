<?php
namespace app\controllers;

use core\interfaces\controllerInterface;
use core\classes\session;

class logout extends base implements controllerInterface
{

    public function index()
    {
        session::kill("user");
        $this->redirect("/");
    }
}

