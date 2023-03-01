<?php
namespace app\models;

use core\abstracts\model;

class Adminuser extends model
{
    public $name;

    public $email;

    public $password;

    public $sys_admin;

    public function init($table = null)
    {
        parent::init("admin_users");
    }
}

