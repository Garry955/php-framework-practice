<?php
namespace app\components;

use core\abstracts\model;
use app\models\User;
use app\models\Picture;
use core\classes\session;

class registerComponent extends model
{

    public function isAvailable($email, $table, $id = 0)
    {
        $available = $this->db->selectWithAnd($table, "id", [
            [
                "key" => "email",
                "value" => $email
            ],
            [
                "key" => "id",
                "value" => $id,
                "op" => "<>"
            ]
        ])->rowCount();
        return $available;
    }

    public function checkName($name, $table, $id = 0)
    {
        return ! $this->db->selectWithAnd($table, "id", [
            [
                "key" => "name",
                "value" => $name
            ],
            [
                "key" => "id",
                "value" => $id,
                "op" => "<>"
            ]
        ])->rowCount();
    }

    /**
     * Generate rotate hash for authentication.
     *
     * @param string $password
     *            Current password.
     * @return string
     */
    public function generatePasswordHash($password)
    {
        if (PHP_VERSION_ID < 50500) {
            $salt = mcrypt_create_iv(22, MCRYPT_DEV_URANDOM);
            $salt = base64_encode($salt);
            $salt = str_replace("+", ".", $salt);
            $salt = "$2y$12$" . $salt . "$";
            $hash = crypt($password, $salt);
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT, [
                "cost" => 12
            ]);
        }
        return $hash;
    }

    public function isPasswordChanged($password, $hash)
    {
        if (PHP_VERSION_ID < 50500) {
            return crypt($password, $hash) != $hash;
        }
        return ! password_verify($password, $hash);
    }

    public function regist($name, $email, $pw, $sex, $code, $picture)
    {
        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->password = self::generatePasswordHash($pw, PASSWORD_BCRYPT);
        $user->active = 0;
        if ($picture) {
            $user->picture = $picture;
        }
        $user->sex = $sex;
        $user->code = $code;
        $user->create(); // lehetne save() is | create( ben átlehet adni a tömböt.)
    }

    public function regPicture($array = [], $id = "")
    {
        $file = [];
        $comp = new uploadComponent();

        $file["path"] = "web/profile-imgs/" . $id . "/" . date("Y/m/d/");

        $uploadOK = true;
        $msgName = self::generatePw(8);
        $file["name"] = $msgName . '-' . $id;
        $targetFile = $file["path"] . $file["name"];

        // common validate
        $type = getimagesize($array["tmp_name"]);
        $file["type"] = $type["mime"];
        if ($file["type"] != "image/jpeg" && $file["type"] != "image/png" && $file["type"] != "image/gif" && $file["type"] != "image/bmp") {
            $message[$msgName] = $file["name"] . ": jpg, gif, png és jpeg típus engedélyezett...";
            $uploadOK = false;
        }
        $size = (float) ($array["size"] / 1024); // KB
        if ($size > 2048) {
            $message[$msgName] = "Maximum 2MB-os képet lehet feltölteni.";
            $uploadOK = false;
        }
        $check = getimagesize($array["tmp_name"]);
        if (! $check) {
            $message[$msgName] = $file["name"] . ": A file nem képfile.";
            $uploadOK = false;
        }
        $file["uploader_id"] = $id;

        // save
        $file["size"] = $array["size"];
        $file["album_id"] = 0;
        if ($uploadOK) {
            if (! is_dir($file["path"])) {
                mkdir($file["path"], 0775, true);
            }
            if (move_uploaded_file($array["tmp_name"], $targetFile)) {
                $message["success"] = 'Kép: ' . $file["name"] . " sikeresen feltöltve.";
                $entity = new Picture();
                $query = $entity->save($file);
            } else {
                $message[$msgName] = $file["name"] . ": nem sikerült feltölteni a képet.";
            }
        }
        session::set("message", $message);
        return $file["name"];
    }

    public function checkMail($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
        return false;
    }

    public function samePwd($pw1, $pw2)
    {
        if ($pw1 == $pw2) {
            return true;
        }
        return false;
    }

    public function generatePw($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i ++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     *
     * @param string $email
     * @param string $table
     */
    public function mailAvailable($email, model $entity)
    {
        $entity::findFirst([
            [
                "key" => "email",
                "value" => $email
            ]
        ]);

        if ($entity) {
            return false;
        }
        return true;
    }
}

