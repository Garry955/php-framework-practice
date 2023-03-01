<?php
namespace app\models;

use core\abstracts\model;

class User extends model
{

    public $name;

    public $email;

    public $password;

    public $active;

    public $picture;

    public $sex;

    public $code;
}

