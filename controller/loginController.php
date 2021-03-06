<?php

use login\Index;
use login\Register;

require_once("controller.php");

require_once(SERVICE_PATH . "/authenticationManager.php");
require_once(VIEW_PATH . "/login/index.php");
require_once(VIEW_PATH . "/login/register.php");

class LoginController extends Controller {
    private $users;

    function __construct(AuthenticationManager $users) {
        $this->users = $users;
    }

    public function index() {
        if ($this->users->isUserLoggedIn()) {
            $this->redirectToHome();
        } else {
            if ($this->hasPostValue("form-id") && $_POST["form-id"] === "login") {
                return $this->doLogin();
            }
            return new Index();
        }
    }

    public function logout() {
        if ($this->users->isUserLoggedIn()) {
            $this->users->logout();
        }
        $this->redirectToHome();
    }

    public function register() {
        if ($this->users->isUserLoggedIn()) {
            $this->redirectToHome();
        } else {
            if ($this->hasPostValue("form-id") && $_POST["form-id"] === "register") {
                return $this->doRegister();
            }
            return new Register();
        }
    }

    private function doLogin() {
        $keep = $this->hasPostValue("keep");
        $user = $_POST["user"];
        $pass = $_POST["pass"];

        if ($this->users->tryLogin($user, $pass, $keep)) {
            if ($this->hasGetValue("redirect")) {
                $redirect = $_GET["redirect"];
                $this->redirectTo($redirect);
            } else {
                $this->redirectToHome();
            }
        } else {
            return new Index("Unable to login");
        }
    }

    private function doRegister() {
        $firstname = $_POST["firstname"];
        $lastname = $_POST["lastname"];
        $email = $_POST["email"];
        $pass = $_POST["pass"];
        $success = $this->users->register($firstname, $lastname, $email, $pass);
        if ($success !== true) {
            return new Register($email, $success);
        }

        if ($this->users->tryLogin($email, $pass, false))
            $this->redirectToHome();
        else
            $this->redirectToLogin();
    }
}