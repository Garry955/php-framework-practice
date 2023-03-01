<?php
namespace app\models;

use core\abstracts\model;

class Menu extends model
{
    public $name;

    public $src;

    public $position;

    public $level;

    public $parent;

    public $need_session;
}

