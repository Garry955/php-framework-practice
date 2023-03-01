<?php
namespace app\controllers;

use core\interfaces\controllerInterface;
use app\components\registerComponent;
use core\classes\session;
use core\classes\mailer;
use app\components\adminComponent;
use app\models\User;

class repassword extends base implements controllerInterface
{

    public function index()
    {
        $this->view->addInlineCss("assets/css/common/repassword.css");
    }

    public function newPassword()
    {
        $email = $_POST["email"];
        $message = [];

        $method = new registerComponent();

        if (isset($_POST["sent"])) {

            $mailAble = $method->isAvailable($email,"users");
            $valid = $method->checkMail($email);

            if (!$valid) {
                $message["mailForm"] = "Hibás e-mail cím formátum!";
                session::set("message", $message);
                $this->redirect("/repassword");
            }

            if (!$mailAble) {
                $message["noUsr"] = "Ilyen e-mail címmel nincs regisztrált felhasználó!";
                session::set("message", $message);
                $this->redirect("/repassword");
            }

            $rawPw = $method->generatePw();
            $newPw = $method->generatePasswordHash($rawPw);


            $sql = $this->db->update("users", [
                "password" => $newPw
            ], [
                [
                    "key" => "email",
                    "value" => $email
                ]
            ]);

            if ($sql) {
                $message["success"] = "Az új jelszót kiküldtük a megadott e-mail címre!";
                $mail = new mailer();

                $select = $this->db->selectWithAnd("users", [
                    "name"
                ], [
                    [
                        "key" => "email",
                        "value" => $email
                    ]
                ]);
                $userName = $select->fetchColumn();

                $subject = "Új jelszó - " . $GLOBALS["config"]["companyName"];

                $msgBody = "Kedves " . $userName . "!<br><br>Kérésére új jelszót generáltunk a(z) " . $email . " e-mail címhez.<br>Az alábbi jelszóval be tud lépni: " . $rawPw . "<br><br>Üdvözlettel:<br>" . $GLOBALS["config"]["companyName"];

                $mail->send($email, $subject, $msgBody);
            } else {
                $message["fail"] = "Nem sikerült új jelszót generálni!";
            }
            if ($message) {
                session::set("message", $message);
            }
            $this->redirect("/");
        }
    }

    public function reactivate()
    {
        $this->view->addInlineCss("assets/css/common/repassword.css");
    }

    public function activate()
    {
        $email = $_POST["email"];
        $code = trim($_POST["code"]);
        $message = [];

        if (isset($_POST["sent"])) {
            $sql = $this->db->selectWithAnd("users", "*", [
                [
                    "key" => "email",
                    "value" => $email
                ]
            ]);

            $params = $sql->fetchObject();

            if ($params) {
                if ($params->active) {
                    $message["isActive"] = "Ez a felhasználó már aktív.";
                    if ($message) {
                        session::set("message", $message);
                    }
                    $this->redirect("/repassword/reactivate");
                } else {
                    if (trim($params->code) == $code) {
                        $sqlUp = $this->db->update("users", [
                            "active" => 1,
                            "code" => 0
                        ], [
                            "email" => $email
                        ]);
                        if ($sqlUp) {
                            $message["success"] = "Sikeresen aktiválta fiókját!";
                            session::set("user", $params);
                            if ($message) {
                                session::set("message", $message);
                            }
                            $this->redirect("/login");
                        } else {
                            $message["fail"] = "Nem sikerült aktiválni fiókját!";
                            if ($message) {
                                session::set("message", $message);
                            }
                            $this->redirect("/repassword/reactivate");
                        }
                    } else {
                        $message["miss"] = "Hibás ellenőrző kód!";
                        if ($message) {
                            session::set("message", $message);
                        }
                        $this->redirect("/repassword/reactivate");
                    }
                }
            } else {
                $message["empty"] = "Ezzel az e-mail címmel nincs regisztrált felhasználó!";
                if ($message) {
                    session::set("message", $message);
                }
                $this->redirect("/repassword/reactivate");
            }
        }
    }

    public function activateUri()
    {
        $this->view->toggleRender();

        $message = [];
        $comp = new adminComponent();

        $id = $comp->getID();

        $select = $this->db->selectWithAnd("users", [
            "*"
        ], [
            [
                "key" => "code",
                "value" => $id
            ]
        ]);
        $user = $select->fetchObject();

        if (! $user) {
            $message["aggree"] = "A hivatkozás már nem létezik!";
            session::set("message", $message);
            session::kill("user");
            $this->redirect("/");
        }

        if ($user->active == 1) {
            $message["curr"] = "A fiók jelenleg aktiválva van!";
            session::set("message", $message);
            session::set("user", $user);
            $this->redirect("/home");
        }

        if ($user) {
            $update = $this->db->update("users", [
                "active" => 1,
                "code" => null
            ], [
                [
                    "key" => "id",
                    "value" => $user->id
                ]
            ]);

            if ($update) {
                session::set("user", $user);
                $message["success"] = "Fiókja sikeresen beaktiválva!";
                session::set("message", $message);
                $this->redirect("/home");
            } else {
                $message["fail"] = "A fiókot nem sikerült beaktiválni!";
                session::set("message", $message);
                $this->redirect("/repassword/reactivate");
            }
        } else {
            $message["aggree"] = "A hivatkozás már nem létezik!";
            session::set("message", $message);
            $this->redirect("/");
        }
    }
}

