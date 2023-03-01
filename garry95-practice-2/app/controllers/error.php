<?php
namespace app\controllers;

use core\interfaces\controllerInterface;
use core\classes\mailer;

class error extends base implements controllerInterface
{

    public function index()
    {}

    public function error404()
    {
        $this->setParams("error", 404);
    }
}
