<?php
namespace app\controllers;

use core\interfaces\controllerInterface;
use app\components\registerComponent;
use core\classes\session;
use core\classes\mailer;
use app\components\uploadComponent;
use app\models\User;

class register extends base implements controllerInterface
{

    public function index()
    {
        $this->view->addInlineCss("assets/css/common/register.css");
    }

    public function reg()
    {
        $name = $_POST["name"];
        $email = $_POST["email"];
        $pw = $_POST["password"];
        $pw2 = $_POST["password2"];
        $sex = $_POST["gender"];
        $picture = NULL;
        $message = [];

        $register = new registerComponent();
        $code = $register->generatePw(10);


        if ($register->isAvailable($email, "users")) {
            $message["email"] = "Ezzel az e-mail címmel már van regisztrált felhasználó!";
        } elseif (! $register->checkMail($email)) {
            $message["email"] = "Hibás e-mail cím formátum. ( example@domain.tld )";
        }
        if (! $register->samePwd($pw, $pw2)) {
            $message["password"] = "A 2 jelszó nem egyezik";
        }

        if ($message) {
            session::set("message", $message);
            $this->redirect("/register");
        }

        $register->regist($name, $email, $pw, $sex, $code, $picture);

        //Upload img
        if ($_FILES["picture"]["name"] !== "") {
            $user = User::findFirst([
                [
                    "key" => "email",
                    "value" => $email
                ]
            ]);
            $id = $user->id;

            $picture = $register->regPicture($_FILES["picture"],$id);

            $user->update([
                "picture" => $picture
            ]);
        }

        $subject = "Sikeres regisztráció! | " . $GLOBALS["config"]["companyName"];
        $regBody = "Kedves " . $name . "!<br> Köszönjük regisztrációját, megerősítheti fiókját az alábbi linkre kattintva: ";
        $regBody .= "<a href=\"http://practice2.lh/repassword/activateUri/" . trim($code) . "\" target=\"blank\">";
        $regBody .= "http://practice2.lh/repassword/activateUri/" . trim($code) . "</a>";
        $regBody .= "<br><br>Üdvözlettel: " . $GLOBALS["config"]["companyName"];

        mailer::send($email, $subject, $regBody);

        $message["success"] = "Sikeres regisztráció!";
        session::set("message", $message);
        $this->redirect("/");
    }

    public function previewImg()
    {
        if ($_FILES["pic-in"]["name"] != '') {
            $test = explode(".", $_FILES["pic-in"]["name"]);
            $extension = end($test);
            $name = rand(100, 999);
//             $location = "web/img/ajax/".$name;
            $location = $_FILES["pic-in"]["tmp_name"];
            move_uploaded_file($_FILES["pic-in"]["name"], $location);

            $html = '<img src="'.$location.'"/>';

            return $html;
        }
    }
}