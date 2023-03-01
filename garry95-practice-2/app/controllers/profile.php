<?php
namespace app\controllers;

use app\controllers\base;
use core\interfaces\controllerInterface;
use core\classes\session;
use core\classes\mailer;
use app\components\loginComponent;
use app\components\registerComponent;
use app\models\User;

class profile extends base implements controllerInterface
{

    public function index()
    {
        $this->view->addInlineCss("assets/css/common/profile.css");

        if (! session::check("user")) {
            $this->redirect("/");
        }
        $this->setParams("user", session::get("user"));
    }

    public function update()
    {
        $this->view->toggleRender();

        $user = session::get("user");
        $message = [];
        $new = [];

        $comp = new loginComponent();
        $reg = new registerComponent();

        $currName = $user->name;
        $currEmail = $user->email;
        $currSex = $user->sex;
        $currPw = $user->password;

        $name = $_POST["name"];
        $email = $_POST["email"];
        $gender = $_POST["gender"];
        $password = $_POST["password"];
        $password2 = $_POST["password2"];

        if ($currName == $name && $currEmail == $email && $currSex == $gender && $password == "") {
            $message["noChange"] = "Nem történt módosítás.";
            session::set("message", $message);
            $this->redirect("/profile");
        }

        if ($password !== $password2) {
            $message["samePw"] = "A két jelszó nem egyezik!";
            session::set("message", $message);
            $this->redirect("/profile");
        } else {
            $pw = $reg->generatePasswordHash($password);
            $new["password"] = $pw;
        }

        if ($email !== $currEmail) {
            $new["email"] = $email;

            $mail = new mailer();

            $mailTo = $email;
            $subject = "E-mail cím változás | " . $GLOBALS["companyName"];
            $msgBody = "Kedves " . $name . "!";
            $msgBody .= "Kérésére belépési és kapcsolat tartási e-mail címe megváltozott a(z) " . $currEmail . " címről.<br><br>";
            $msgBody .= "Az új címe a(z) " . $email . ".";
            $msgBody .= "Üdvözlettel,<br><br>" . $GLOBALS["companyName"];
            $mail->send($mailTo, $subject, $msgBody);
        }

        if (! $message) {
            $new["name"] = $name;
            $new["email"] = $email;
            $new["sex"] = $gender;

//             $this->debugger($new);
            $sql = $this->db->update("users", $new, [
                [
                    "key" => "id",
                    "value" => $user->id
                ]
            ]);

            if ($sql) {
                $usr = new User();

                $usr = User::findFirst([
                    [
                        "key" => "email",
                        "value" => $email
                    ]
                ]);

                session::set("user", $usr);

                $message["success"] = "Sikeres adatmódosítás!";
                session::set("message", $message);
                $this->redirect("/profile");
            } else {
                $message["fail"] = "Nem sikerült az adatmódosítás!";
                session::set("message", $message);
                $this->redirect("/profile");
            }
        }
    }

    public function deactivate()
    {
        $user = session::get("user");
        $message = [];
        $comp = new registerComponent();
        $datas = User::findFirst($user->id);

        if (isset($_POST["deac"])) {
            $code = $comp->generatePw();

            $sql = $datas->save([
                "active" => 0,
                "code" => $code
            ]);

            if ($sql) {
                $mail = new mailer();
                $msgBody = "Kedves " . $user->name . "!<br><br> Fiókját sikeresen inaktiválta!<br>";
                $msgBody .= "Amennyiben szeretné újra aktiválni";
                $msgBody .= "a <a href=\"http://practice2.lh/repassword/activateUri/".$code."\" target=\"_blank\">";
                $msgBody .= "http://practice2.lh/repassword/activateUri/".$code."</a> hivatkozással teheti meg.";
                $msgBody .= "<br><br>Üdvözlettel:<br>" . $GLOBALS["config"]["companyName"] . "";
                $mail->send($user->email, "Sikeresen inaktiválta fiókját!", $msgBody);
                $message["success"] = "A fiókot inaktiváltuk!";
                session::set("message", $message);
                session::kill("user");
                $this->redirect("/");
            } else {
                $message["failDeac"] = "Nem sikerült a fiókot inaktiválni!";
            }
        }
        if ($message) {
            session::set("message", $message);
        }
        $this->redirect("/");
    }

    public function remove()
    {
        $user = session::get("user");
        $message = [];

        if (isset($_POST["remove"])) {
            $sqlDel = $this->db->delete("users", [
                "id" => $user->id
            ]);
            if ($sqlDel) {
                $mail = new mailer();
                $msgBody = "Kedves " . $user->name . "!<br><br> Fiókját sikeresen törölte!<br> Amennyiben szeretne újra regisztrálni a <a href=\"http://practice2.lh/register/\" target=\"_blank\">www.practice2.lh/register/</a> oldalon teheti meg!<br><br>Üdvözlettel:<br>" . $GLOBALS["config"]["companyName"] . "";
                $mail->send($user->email, "Sikeresen törölte fiókját!", $msgBody);
                $message["successRm"] = "Fiókját töröltük!";
            } else {
                $message["failRm"] = "Nem sikerült törölni fiókját!";
            }
        }
        if ($message) {
            session::set("message", $message);
        }
        $this->redirect("/logout");
    }
}
