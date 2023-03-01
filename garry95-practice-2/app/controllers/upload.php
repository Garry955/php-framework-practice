<?php
namespace app\controllers;

use core\interfaces\controllerInterface;
use core\classes\session;
use app\components\uploadComponent;
use app\components\registerComponent;
use app\models\Picture;
use app\models\Video;

class upload extends base implements controllerInterface
{

    public function index()
    {
        $this->setParams("user", session::get("user"));
        $this->view->addInlineCss("assets/css/common/upload.css");
    }

    public function upload()
    {
        $user = session::get("user");
        $id = $user->id;
        $files = $message = [];
        $comp = new uploadComponent();
        $gen = new registerComponent();

        if (isset($_POST["sent"])) {
            if (isset($_FILES["picture"])) {
                $file["path"] = "web/img/" . $id . "/" . date("Y/m/d/");
                $datas = $comp->getArray($_FILES["picture"]);

                if (! isset($_FILES["picture"])) {
                    $message["fail"] = "Nincs fájl kiválasztva.";
                    session::set("message", $message);
                    $this->redirect("/upload");
                }
                foreach ($datas as $picture) {
                    $uploadOK = true;
                    $rnd = $gen->generatePw(8);
                    $file["name"] = $rnd . '-' . $comp->friendlyFilename($picture["name"]);
                    $targetFile = $file["path"] . $file["name"];

                    // common validate
                    $type = getimagesize($picture["tmp_name"]);
                    $file["type"] = $type["mime"];
                    if ($file["type"] != "image/jpeg" && $file["type"] != "image/png" && $file["type"] != "image/gif" && $file["type"] != "image/bmp") {
                        $message[$msgName] = $file["name"] . ": jpg, gif, png és jpeg típus engedélyezett...";
                        $uploadOK = false;
                    }
                    $size = (float) ($picture["size"] / 1024); // KB
                    if ($size > 2048) {
                        $message[$msgName] = "Maximum 2MB-os képet lehet feltölteni.";
                        $uploadOK = false;
                    }
                    $check = getimagesize($picture["tmp_name"]);
                    if (! $check) {
                        $message[$msgName] = $file["name"] . ": A file nem képfile.";
                        $uploadOK = false;
                    }
                    if ($_POST["tags"]) {
                        $file["tags"] = $_POST["tags"];
                    }

                    // save
                    $file["size"] = $picture["size"];
                    $file["uploader_id"] = $id;
                    if ($uploadOK) {
                        if (! is_dir($file["path"])) {
                            mkdir($file["path"], 0775, true);
                        }
                        if (move_uploaded_file($picture["tmp_name"], $targetFile)) {
                            $message["success" . "$rnd"] = 'Kép: ' . $file["name"] . " sikeresen feltöltve.";
                            $entity = new Picture();
                            $query = $entity->save($file);
                        } else {
                            $message[$msgName] = $file["name"] . ": nem sikerült feltölteni a képet.";
                        }
                    }
                }
            }
            session::set("message", $message);
            $this->redirect("/upload");
        }
    }

    public function video()
    {
        $new = $message = [];
        $entity = new Video();
        $comp = new uploadComponent();

        if (isset($_POST["sent"])) {
            if ($_POST["name"] && $_POST["source"]) {
                $embed = $comp->toEmbed($_POST["source"]);

                $new["name"] = $_POST["name"];
                $new["source"] = $embed;
            }
            if ($entity->save($new)) {
                $message["success"] = "Sikeres feltöltés";
                session::set("message", $message);
                $this->redirect("/upload");
            } else {
                $message["fail"] = "Sikertelen feltöltés";
                session::set("message", $message);
                $this->redirect("/upload");
            }
        }
    }
}
