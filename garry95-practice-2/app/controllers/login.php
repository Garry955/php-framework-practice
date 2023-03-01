<?php
namespace app\controllers;

use app\components\loginComponent;
use core\classes\session;

class login extends base
{

    public function index()
    {

        $login = new \app\components\loginComponent();

        $message = [];
        if (isset($_POST["sent"])) {
            if ($login->isValid($_POST["email"], $_POST["password"])) {
                $this->redirect("/home");
            } else {
                $message["loginFail"] = "Hibás e-mail cím vagy jelszó.";
            }
        }

        if ($message) {
            session::set("message", $message);
            $this->redirect("/");
        }
    }
}

