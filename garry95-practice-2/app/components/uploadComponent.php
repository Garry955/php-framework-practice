<?php
namespace app\components;

use core\abstracts\model;
use app\models\Picture;
use core\classes\session;

class uploadComponent extends model
{

    public function friendlyFilename($str, $replace = [], $delimiter = "-")
    {
        if ($str == "") {
            return "";
        }
        setlocale(LC_ALL, "hu_HU.UTF8");
        if ($replace) {
            $str = str_replace((array) $replace, " ", $str);
        }
        $pieces = explode(".", mb_strtolower($str));
        $ext = array_pop($pieces);
        $name = implode(".", $pieces);
        $clean = iconv(mb_detect_encoding($name), "ASCII//TRANSLIT", $name);
        $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", "", $clean);
        $clean = trim($clean, "-");
        $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
        return $clean . "." . $ext;
    }

    public function getArray($file)
    {
        foreach ($file as $key => $datas) {
            foreach ($datas as $index => $value) {
                if (! isset($files[$index])) {
                    $files[$index] = [];
                }
                $files[$index][$key] = $value;
            }
        }
        return $files;
    }

    public function upload($admin = false)
    {
        $files = $message = [];
        $get = new adminComponent();
        $id = $get->getID();
        $append = "";

        $gen = new registerComponent();
        $rnd = $gen->generatePw(5);

        if (! isset($_FILES["picture"])) {
            $message["fail"] = "Nincs fájl kiválasztva.";
            session::set("message", $message);
            return false;
        }
        if ($admin) {
            $append = "admin/";
        }
        $file["path"] = "web/img/" . $append . session::get("admin")->id . "/" . date("Y/m/d/");
        $datas = self::getArray($_FILES["picture"]);

        foreach ($datas as $picture) {
            $uploadOK = true;
            $msgName = $gen->generatePw(8);
            $file["name"] = $rnd . '-' . self::friendlyFilename($picture["name"]);
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
            if (isset($_POST["uploader"])) {
                $file["uploader_id"] = $_POST["uploader"];
            }

            // save
            $file["size"] = $picture["size"];
            $file["album_id"] = $id;
            if ($uploadOK) {
                if (! is_dir($file["path"])) {
                    mkdir($file["path"], 0775, true);
                }
                if (move_uploaded_file($picture["tmp_name"], $targetFile)) {
                    $message["success"] = 'Kép: ' . $file["name"] . " sikeresen feltöltve.";
                    $entity = new Picture();
                    $query = $entity->save($file);
                } else {
                    $message[$msgName] = $file["name"] . ": nem sikerült feltölteni a képet.";
                }
            }
            session::set("message", $message);
        }
    }

    /**
     * Coverts a direct youtube link to an embed src
     *
     * @param string $link
     */
    public function toEmbed($link = "")
    {
        $parts = [];
        $search = "watch?v=";
        $replace = "embed/";
        $final = "";
        $list = "&list";

        if ($link) {
            $parts = explode($list, $link);
            $friendlyUrl = $parts[0];

            $final = str_replace($search, $replace, $friendlyUrl);
        }
        return $final;
    }

    /**
     * Explodes tags by wspace and put into an array
     *
     * @param string $tags
     */
    public function prepTags()
    {
        $entity = new Picture();

        $tags = $entity->tags;

        $arr = explode(" ", $tags, 100);

        return $arr;
    }
}

