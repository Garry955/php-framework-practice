<?php
namespace app\controllers;

use core\interfaces\controllerInterface;
use app\components\loginComponent;
use app\models\User;
use core\classes\session;
use app\models\Adminuser;
use app\models\basicdata;
use app\models\Menu;
use app\components\adminComponent;
use app\models\Album;
use app\components\uploadComponent;
use app\models\Picture;

class admin extends adminbase implements controllerInterface
{

    public function index()
    {
        $this->route["method"] = "login";

        $login = new loginComponent();

        if (isset($_POST["sent"])) {
            $email = $_POST["email"];
            $pw = md5($_POST["password"]);
            if ($login->isAdmin($email, $pw)) {
                $this->redirect("/admin/dashboard");
            } else {
                $this->setParams("error", "Hibás e-mail cím vagy jelszó.");
            }
        }

        if (session::check("admin")) {
            $this->redirect("/admin/dashboard");
        }
    }

    public function dashboard()
    {}

    public function logout()
    {
        session::kill("admin");
        $this->redirect("/admin");
    }

    public function users()
    {
        $users = User::find();
        $this->setParams("users", $users);
    }

    public function galleries()
    {
        $datas = Album::find();
        $this->setParams("datas", $datas);
    }

    public function menus()
    {
        $menus = Menu::find();
        $this->setParams("menus", $menus);
    }

    public function adminUsers()
    {
        $users = Adminuser::find();
        $this->setParams("users", $users);
    }

    public function basicdatas()
    {
        $datas = Basicdata::find();
        $this->setParams("datas", $datas);
    }

    public function addUser()
    {
        $this->view->addInlineCss("assets/css/admin/common/add.css");

        $comp = new adminComponent();

        $return = $comp->userEditor(new User());
        if ($return) {
            $this->redirect("/admin/users");
        }
    }

    public function addAdmin()
    {
        $this->view->addInlineCss("assets/css/admin/common/add.css");

        $comp = new adminComponent();

        $return = $comp->adminEditor(new Adminuser());
        if ($return) {
            $this->redirect("/admin/adminUsers");
        }
    }

    public function addData()
    {
        $this->view->addInlineCss("assets/css/admin/common/add.css");

        $comp = new adminComponent();

        $return = $comp->dataEditor(new Basicdata());
        if ($return) {
            $this->redirect("/admin/basicdatas");
        }
    }

    public function addMenu()
    {
        $this->view->addInlineCss("assets/css/admin/common/add.css");

        $comp = new adminComponent();

        $datas = Menu::find();

        $this->setParams("datas", $datas);

        $return = $comp->menuEditor(new Menu());
        if ($return) {
            $this->redirect("/admin/menus");
        }
    }

    public function addGallery()
    {
        $this->view->addInlineCss("assets/css/admin/common/add.css");

        $comp = new adminComponent();

        $return = $comp->albumEditor(new Album());
        if ($return) {
            $this->redirect("/admin/galleries");
        }
    }

    public function editUser()
    {
        $this->view->addInlineCss("assets/css/admin/common/edit.css");
        $comp = new adminComponent();
        $id = $comp->getID();
        $user = User::findFirst($id);
        $this->setParams("user", $user);
        $return = $comp->userEditor($user);
        if ($return) {
            $this->redirect($_SERVER["REQUEST_URI"]);
        }
    }

    public function editAdmin()
    {
        $this->view->addInlineCss("assets/css/admin/common/edit.css");

        $comp = new adminComponent();
        $id = $comp->getID();
        $user = Adminuser::findFirst($id);
        $this->setParams("user", $user);
        $return = $comp->adminEditor($user);
        if ($return) {
            $this->redirect($_SERVER["REQUEST_URI"]);
        }
    }

    public function editMenu()
    {
        $this->view->addInlineCss("assets/css/admin/common/edit.css");

        $comp = new adminComponent();
        $id = $comp->getID();
        $datas = Menu::findFirst($id);
        $this->setParams("datas", $datas);

        $menus = Menu::find();
        $this->setParams("menus", $menus);

        $return = $comp->menuEditor($datas);
        if ($return) {
            $this->redirect($_SERVER["REQUEST_URI"]);
        }
    }

    public function editData()
    {
        $this->view->addInlineCss("assets/css/admin/common/edit.css");

        $comp = new adminComponent();
        $id = $comp->getID();
        $datas = Basicdata::findFirst($id);
        $this->setParams("datas", $datas);
        $return = $comp->dataEditor($datas);
        if ($return) {
            $this->redirect($_SERVER["REQUEST_URI"]);
        }
    }

    public function editGallery()
    {
        $this->view->addInlineCss("assets/css/admin/common/edit.css");

        $albums = Album::find();
        $this->setParams("albums", $albums);

        $comp = new adminComponent();
        $id = $comp->getID();

        $uploaders = User::find();
        $this->setParams("uploaders", $uploaders);

        $datas = Album::findFirst($id);
        $this->setParams("datas", $datas);
        $return = $comp->albumEditor($datas);
        if ($return) {
            $this->redirect($_SERVER["REQUEST_URI"]);
        }

        if (isset($_POST["upSent"])) {
            $img = new uploadComponent();
            $issue = $img->upload(true);
        }

        $pictures = Picture::find([
            [
                "key" => "album_id",
                "value" => $id
            ]
        ]);
        $this->setParams("pictures", $pictures);
    }

    public function toggle()
    {
        $location = "/admin/users";
        if (isset($_SERVER["HTTP_REFERER"])) {
            $referer = parse_url($_SERVER["HTTP_REFERER"]);
            if (! isset($referer["host"]) || $referer["host"] == $_SERVER["HTTP_HOST"]) {
                $location = $referer["path"];
            }
        }
        $comp = new adminComponent();
        $comp->toggle();

        $this->redirect($location);
    }

    public function togglePanel()
    {
        $url = $_SERVER["REQUEST_URI"];
        $parts = explode("/", $url);
        $id = end($parts);
        self::toggle();
    }

    public function deleteUser()
    {
        $comp = new adminComponent();

        $id = $comp->getID();
        $redirect = "/admin/users";

        $user = User::findFirst($id);
        if (! $user) {
            $message["noid"] = "Nem létező id.";
            session::set("message", $message);
            $this->redirect($redirect);
        }

        $comp->deleteData($user);
        $this->redirect($redirect);
    }

    public function deleteAdmin()
    {
        $comp = new adminComponent();

        $id = $comp->getID();
        $redirect = "/admin/adminUsers";

        $user = Adminuser::findFirst($id);
        if (! $user) {
            $message["noid"] = "Nem létező id.";
            session::set("message", $message);
            $this->redirect($redirect);
        }

        $comp->deleteData($user);
        $this->redirect($redirect);
    }

    public function deleteData()
    {
        $comp = new adminComponent();

        $id = $comp->getID();
        $redirect = "/admin/basicdatas";

        $user = Basicdata::findFirst($id);
        if (! $user) {
            $message["noid"] = "Nem létező id.";
            session::set("message", $message);
            $this->redirect($redirect);
        }

        $comp->deleteData($user);
        $this->redirect($redirect);
    }

    public function deleteMenu()
    {
        $comp = new adminComponent();

        $id = $comp->getID();
        $redirect = "/admin/menus";

        $user = Menu::findFirst($id);
        if (! $user) {
            $message["noid"] = "Nem létező id.";
            session::set("message", $message);
            $this->redirect($redirect);
        }

        $comp->deleteData($user);
        $this->redirect($redirect);
    }

    public function deleteGallery()
    {
        $comp = new adminComponent();

        $id = $comp->getID();
        $redirect = "/admin/galleries";

        $datas = Album::findFirst($id);
        if (! $datas) {
            $message["noid"] = "Nem létező id.";
            session::set("message", $message);
            $this->redirect($redirect);
        }

        $comp->deleteData($datas);
        $this->redirect($redirect);
    }
}

