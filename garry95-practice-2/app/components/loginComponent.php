<?php
namespace app\components;

use core\abstracts\model;
use core\classes\session;
use app\models\User;
use app\models\Adminuser;

class loginComponent extends model
{

    public function isPasswordValid($password, $hash)
    {
        if (PHP_VERSION_ID < 50500) {
            return crypt($password, $hash) == $hash;
        }
        return password_verify($password, $hash);
    }

    public function isValid($email, $password)
    {
        $user = User::findFirst([
            [
                "key" => "email",
                "value" => $email
            ]
        ]);
        if ($user && $user->active && $this->isPasswordValid($password, $user->password)) {
            session::set("user", $user);
            return true;
        }
        return false;
    }

    public function isAdmin($email, $pw)
    {
        $admin = Adminuser::findFirst([
            [
                "key" => "email",
                "value" => $email
            ],
            [
                "key" => "password",
                "value" => $pw
            ]
        ]);
        if ($admin) {
            session::set("admin", $admin);
            return true;
        }
        return false;
    }
}

