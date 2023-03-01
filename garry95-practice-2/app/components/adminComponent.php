<?php
namespace app\components;

use core\abstracts\model;
use core\classes\session;
use app\models\User;
use app\models\Adminuser;
use core\classes\mailer;
use app\models\Basicdata;
use app\models\Menu;
use app\models\Album;

class adminComponent extends model
{

    /**
     * Returns the ID by cutting from the end of the URL
     *
     * @return string $id
     */
    public function getID()
    {
        $url = $_SERVER["REQUEST_URI"];
        $parts = explode("/", $url);
        $id = end($parts);

        return $id;
    }

    public function userEditor(User $entity)
    {
        if (! isset($_POST["sent"])) {
            return false;
        }
        $datas = $message = [];
        $needPw = $sendMail = true;
        $validate = new registerComponent();
        if ($entity->id) { // Edit validation
            $needPw = ! empty($_POST["password"]);
        }
        // Common validation
        if ($validate->checkMail($_POST["email"])) {
            if ($validate->isAvailable($_POST["email"], $entity->getTable(), $entity->id)) {
                $datas["email"] = $_POST["email"];
            } else {
                $message["email"] = "Ezzel az e-mail címmel már regisztráltak!";
            }
        } else {
            $message["email"] = "Hibás e-mail cím formátum!";
        }
        if ($needPw && $validate->samePwd($_POST["password"], $_POST["password2"])) {
            $datas["password"] = $validate->generatePasswordHash($_POST["password"]);
        } elseif ($needPw && ! $validate->samePwd($_POST["password"], $_POST["password2"])) {
            $message["password"] = "A két jelszó nem egyezik!";
        }

        // Save datas
        $datas["sex"] = (int) $_POST["gender"];
        $datas["name"] = $_POST["name"];
        if (! $message) {
            if ($entity->id) {
                $message["success"] = "Sikeres adatmódosítás";
                $sendMail = isset($datas["email"]) || ($needPw && $validate->isPasswordChanged($_POST["password"], $entity->password));
                $subject = "Belépési adatok megváltoztak | " . $GLOBALS["config"]["companyName"];
                $msgBody = "Kedves " . $datas["name"] . "!<br>....";
                $msgBody .= "<b>E-mail cím:</b> " . $_POST["email"] . "<br>";
                $msgBody .= "<b>Jelszó:</b> " . ($needPw ? $_POST["password"] : "jelenleg használatban lévő") . "<br>";
                $msgBody .= "....";
            } else {
                $message["success"] = "Az adat sikeresen rögzítve lett az adatbázisba.";
                $subject = "Regisztráció | " . $GLOBALS["config"]["companyName"];
                $msgBody = "Kedves " . $datas["name"] . "!<br>....";
                $msgBody .= "<b>E-mail cím:</b> " . $_POST["email"] . "<br>";
                $msgBody .= "<b>Jelszó:</b> " . $_POST["password"] . "<br>";
                $msgBody .= "....";
            }
            if ($sendMail) {
                mailer::send($entity->id ? $entity->email : $datas["email"], $subject, $msgBody);
            }
            $entity->save($datas);
        }
        session::set("message", $message);
        return true;
    }

    public function adminEditor(Adminuser $entity)
    {
        if (! isset($_POST["sent"])) {
            return false;
        }

        $datas = $message = [];
        $needPw = $sendMail = true;
        $validate = new registerComponent();

        if ($entity->id) { // edit validation
            $needPw = ! empty($_POST["password"]);
        }

        // Common validation
        if ($validate->checkMail($_POST["email"])) {
            if ($validate->isAvailable($_POST["email"], $entity->getTable(), $entity->id)) {
                $datas["email"] = $_POST["email"];
            } else {
                $message["email"] = "Ezzel az e-mail címmel már regisztráltak!";
            }
        } else {
            $message["email"] = "Hibás e-mail cím formátum!";
        }
        if ($needPw && $validate->samePwd($_POST["password"], $_POST["password2"])) {
            $datas["password"] = $validate->generatePasswordHash($_POST["password"]);
        } elseif ($needPw && ! $validate->samePwd($_POST["password"], $_POST["password2"])) {
            $message["password"] = "A két jelszó nem egyezik!";
        }
        // Save datas
        $datas["name"] = $_POST["name"];
        if (! $message) {
            if ($entity->id) {
                $message["success"] = "Sikeres adatmódosítás";
                $sendMail = isset($datas["email"]) || ($needPw && $validate->isPasswordChanged($_POST["password"], $entity->password));
                $subject = "Belépési adatok megváltoztak | " . $GLOBALS["config"]["companyName"];
                $msgBody = "Kedves " . $datas["name"] . "!<br>....";
                $msgBody .= "<b>E-mail cím:</b> " . $_POST["email"] . "<br>";
                $msgBody .= "<b>Jelszó:</b> " . ($needPw ? $_POST["password"] : "jelenleg használatban lévő") . "<br>";
                $msgBody .= "....";
            } else {
                $message["success"] = "Az adat sikeresen rögzítve lett az adatbázisba.";
                $subject = "Regisztráció | " . $GLOBALS["config"]["companyName"];
                $msgBody = "Kedves " . $datas["name"] . "!<br>....";
                $msgBody .= "<b>E-mail cím:</b> " . $_POST["email"] . "<br>";
                $msgBody .= "<b>Jelszó:</b> " . $_POST["password"] . "<br>";
                $msgBody .= "....";
            }
            if ($sendMail) {
                mailer::send($entity->id ? $entity->email : $datas["email"], $subject, $msgBody);
            }
            $entity->save($datas);
        }
        session::set("message", $message);
        return true;
    }

    public function dataEditor(Basicdata $entity)
    {
        if (! isset($_POST["sent"])) {
            return false;
        }
        $datas = $message = [];
        $validate = new registerComponent();

        // common validation
        if ($validate->checkName($_POST["name"], $entity->getTable(), $entity->id)) {
            $datas["name"] = $_POST["name"];
        } else {
            $message["name"] = "Ilyen nevű adat már létezik az adatbázisban.";
        }
        // save datas
        $datas["value"] = $_POST["value"];
        $datas["required"] = $_POST["required"];
        if (! $message) {
            if ($entity->id) {
                $message["success"] = "Sikeres adatmódosítás";
            } else {
                $message["success"] = "Az adat sikeresen rögzítve lett az adatbázisba.";
            }
            $entity->save($datas);
        } else {
            session::set("message", $message);
            return false;
        }
        session::set("message", $message);
        return true;
    }

    public function menuEditor(Menu $entity)
    {
        if (! isset($_POST["sent"])) {
            return false;
        }
        $datas = $message = [];
        $validate = new registerComponent();

        // common validation
        if ($validate->checkName($_POST["name"], $entity->getTable(), $entity->id)) {
            $datas["name"] = $_POST["name"];
        } else {
            $message["name"] = "Ilyen nevű menü már létezik az adatbázisban.";
        }
        if ($_POST["position"] == "footer") {
            $datas["level"] = 0;
        } else {
            $datas["level"] = 1;
        }
        if ($_POST["level"] > 0) {
            if (empty($_POST["parent"])) {
                $message["parent"] = "Ha második szintű a menü, meg kell adni a szülőjét is.";
            } else {
                $datas["parent"] = $_POST["parent"];
            }
        }

        // save datas
        $datas["src"] = $_POST["src"];
        $datas["position"] = $_POST["position"];
        $datas["need_session"] = $_POST["need_session"];
        if (! $message) {
            if ($entity->id) {
                $message["success"] = "Sikeres adatmódosítás";
            } else {
                $message["success"] = "A menü sikeresen rögzítve lett az adatbázisba.";
            }
            $entity->save($datas);
        } else {
            session::set("message", $message);
            return false;
        }
        session::set("message", $message);
        return true;
    }

    public function albumEditor(Album $entity)
    {
        if (! isset($_POST["sent"])) {
            return false;
        }

        $datas = $message = [];
        $validate = new registerComponent();

        // common validation
        if ($validate->checkName($_POST["name"], $entity->getTable(), $entity->id)) {
            $datas["name"] = $_POST["name"];
        } else {
            $message["name"] = "Ilyen nevű galéria már létezik az adatbázisban.";
        }

        // save datas
        $datas["uri"] = $_POST["uri"];
        $datas["cover"] = $_POST["cover"];

        if (! $message) {
            if ($entity->id) {
                $message["success"] = "Sikeres adatmódosítás";
            } else {
                $message["success"] = "A menü sikeresen rögzítve lett az adatbázisba.";
            }
            $entity->save($datas);
        } else {
            session::set("message", $message);
            return false;
        }
        session::set("message", $message);
        return true;
    }

    /**
     * Delete by given entity
     *
     * @param model $entity
     * @param string $redirect
     */
    public function deleteData(model $entity)
    {
        $message = [];

        $id = self::getID();

        if ($entity->delete()) {
            $message["success"] = 'A ' . $id . '-s ID alatti adat sikeresen törölve.';
        } else {
            $message["fail"] = "Nem sikerült törölni az adatot";
        }
        session::set("message", $message);
    }

    public function toggle()
    {
        $id = self::getID();

        $message = [];

        $user = User::findFirst($id);

        if ($user) {
            $state = $user->active ? 0 : 1;
            $user->update([
                "active" => $state
            ]);
            if ($user->active == $state) {
                $message["success"] = "A " . $user->id . " ID alatti felhasznáó sikeresen " . (! $state ? "in" : "") . "aktiválva.";
            } else {
                $message["fail"] = "Nem sikerült " . (! $state ? "in" : "") . "aktiválni a " . $user->id . " ID alatti felhasználót.";
            }
        }
        session::set("message", $message);
    }
}

