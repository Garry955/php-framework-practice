<?php
namespace app\models;

use core\abstracts\model;

class Picture extends model
{

    public $path;

    public $name;

    public $size;

    public $type;

    public $uploader_id;

    public $tags;
}

