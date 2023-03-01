<?php
namespace app\controllers;

use core\interfaces\controllerInterface;
use core\classes\session;
use app\models\Video;

class gallery extends base implements controllerInterface
{

    public function index()
    {
        $usr = session::get("user");
        $id = session::get("user")->id;

        $this->setParams("user", $usr);

        $this->view->addInlineCss("assets/css/common/gallery.css");
        $sql = $this->db->selectWithAnd("pictures", [
            "path",
            "name",
            "tags"
        ], [
            [
                "key" => "uploader_id",
                "value" => $id
            ]
        ]);

        $userImgs = $sql->fetchAll(\PDO::FETCH_ASSOC);

        $this->setParams("images", $userImgs);

        $video = Video::find();
        $this->setParams("videos", $video);

    }
}

