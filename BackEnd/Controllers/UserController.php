<?php

/**
 * Description of UserController
 *  
 * @author AsuRaHan
 */
class UserController extends Controller{
    private $User;
    private $TPL;
    public function __construct() {
        parent::__construct();
        $this->User = new UserModel();
        
        $path = $this->Config()['App_Templates_Dir'];
        $this->TPL = new Template();
        
        if ($this->User->GetCurrentUser()) {
            $path = $this->Config()['App_Templates_Dir'];
            $this->TPL->SetWivePath($path.'IndexController'.DS);
            $this->TPL->NavBar = $this->TPL->execute('navbar.html');
            $this->TPL->SetWivePath($path . __CLASS__ . DS);
        }
        
        $this->TPL->SetWivePath($path . __CLASS__ . DS);
    }
    public function ActionIndex($param=null) {
        $UserVars = $this->User->GetCurrentUser();
        if(!$UserVars){
            return $this->ActionLogin();
        }
        
       
//        var_dump($UserVars);
        $this->TPL->VarSetArray($UserVars);
        $this->TPL->content = $this->TPL->execute('UserCard.html');
        return $this->Generate($this->TPL);
    }
    public function ActionLogout() {
        Session::destroy();
        $this->TPL->NavBar ='';
        return $this->ActionLogin();
    }
    public function ActionLogin() {
        $this->TPL->title ='Вход пользователя';
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            return $this->UserPostLogin();
        } else {
            
            $this->TPL->content =  $this->TPL->execute('FormLogin.html');
            return $this->Generate($this->TPL);
        }
    }
    private function UserPostLogin(){
        $ErrorMsg = "";
        
//        $reCaptchaResponse = filter_input(INPUT_POST, "g-recaptcha-response", FILTER_SANITIZE_STRING);
//        $reCaptchaURl = 'https://www.google.com/recaptcha/api/siteverify';
//        $reCaptchaKey = '6LeWUSIUAAAAADoe0WV2kupo4UeW-9OXAdMXx53f';
//        $reCaptchaPOST = $reCaptchaURl.'?secret='.$reCaptchaKey.'&response='.$reCaptchaResponse.'&remoteip='.$_SERVER['REMOTE_ADDR'];
//        
//        $ret = json_decode(file_get_contents($reCaptchaPOST));
//        if($ret->success == false){
//            $ErrorMsg = '<p class="error">Капча введена не верно</p>';
//        }
        $email = filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);
        
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        $this->TPL->email = $email;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Not a valid email
            $ErrorMsg .= '<p class="error">Введенный адрес электронной почты <p>{'.$email.'}</p> не является действительным</p>';
        }
//        $eml = $this->user->ChekMail($email);
        if(!$this->User->ChekMail($email)){
            $ErrorMsg .= '<p class="error">Введенный адрес электронной почты не используется на сайте</p>';
        }

        $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_STRING);
//        if (strlen($password) <= 4) {
//            // The hashed pwd should be 128 characters long.
//            // If it's not, something really odd has happened
//            $ErrorMsg .= '<p class="error">Неверная конфигурация пароля</p>';
////            $ErrorMsg .= '<p class="error">'.strlen($password).'</p>';
//        }
        if (empty($ErrorMsg)) {
            //ошибок нет, теперь регистрируем
//            $this->TPL->uid = $this->User->CreateUser($email, $password, $login, $name, $middlename, $surname);
            if($this->User->login($email, $password)){
                $this->TPL->content = $this->TPL->execute('LoginFinish.html');
            } else {
                $ErrorMsg .= '<p class="error">Введенный пароль неверен. попробуйте еще раз</p>';
                $this->TPL->ErrorMsg = $ErrorMsg;
                $this->TPL->content = $this->TPL->execute('FormLogin.html');
            }
            
            return $this->Generate($this->TPL);
        }
//        echo $ErrorMsg;
        $this->TPL->ErrorMsg = $ErrorMsg;
        $this->TPL->content = $this->TPL->execute('FormLogin.html');
        return $this->Generate($this->TPL);
    }
    
    
    public function ActionRegistre() {
        $this->TPL->title ='Регистрация пользователя';
        if ($_SERVER["REQUEST_METHOD"]=="POST") {
            return $this->UserPostRegistre();
//            return TRUE;
        } else {
//            $this->TPL->name = 'David';
//            $this->TPL->middlename = 'Sukerman';
//            $this->TPL->surname = 'Ogly';
//            $this->TPL->email = 'example@mail.com';
//            $this->TPL->login = 'test';
//            $this->TPL->password = '123456';
            $this->TPL->content = $this->TPL->execute('FormRegistre.html');
            return $this->Generate($this->TPL);
        }
        
    }
    private function UserPostRegistre(){
//        echo '<pre>';
//        $json = json_decode(file_get_contents('php://input'));
        $ErrorMsg = "";
//        $reCaptchaResponse = filter_input(INPUT_POST, "g-recaptcha-response", FILTER_SANITIZE_STRING);
//        $reCaptchaURl = 'https://www.google.com/recaptcha/api/siteverify';
//        $reCaptchaKey = '6LeWUSIUAAAAADoe0WV2kupo4UeW-9OXAdMXx53f';
//        $reCaptchaPOST = $reCaptchaURl . '?secret=' . $reCaptchaKey . '&response=' . $reCaptchaResponse . '&remoteip=' . $_SERVER['REMOTE_ADDR'];
//
//        $ret = json_decode(file_get_contents($reCaptchaPOST));
//        if ($ret->success == false) {
//            $ErrorMsg .= '<p class="error">reКапча введена не верно</p>';
//        }
        
        $email = filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);

        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        $this->TPL->email = $email;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Not a valid email
            $ErrorMsg .= '<p class="error">Введенный адрес электронной почты не является действительным</p>';
        }
        
        if($this->User->ChekMail($email)){
            $ErrorMsg .= '<p class="error">Введенный адрес электронной почты уже используется</p>';
        }
        
        $login = filter_input(INPUT_POST, "login", FILTER_SANITIZE_STRING);
        $this->TPL->login = $login;
        if ($this->User->ChekUserLogin($login)) {
            $ErrorMsg .= '<p class="error">Введенный логин уже используется. попробуйте другой</p>';
        }
        $name = filter_input(INPUT_POST, "name", FILTER_SANITIZE_STRING);
        $this->TPL->name = $name;
        $middlename = filter_input(INPUT_POST, "middlename", FILTER_SANITIZE_STRING);
        $this->TPL->middlename = $middlename;
        $surname = filter_input(INPUT_POST, "surname", FILTER_SANITIZE_STRING);
        $this->TPL->surname = $surname;
        
        $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_STRING);
        $passwordonf = filter_input(INPUT_POST, "passwordonf", FILTER_SANITIZE_STRING);
        if ($passwordonf != $password) {
            $ErrorMsg .= '<p class="error">Повторный пароль введен не верно!</p>';
        }
        
        if (empty($ErrorMsg)) {
            //ошибок нет, теперь регистрируем
            $this->TPL->uid = $this->User->CreateUser($email, $password, $login, $name, $middlename, $surname);

            $this->TPL->content = $this->TPL->execute('RegFinish.html');
            return $this->Generate($this->TPL);
            
        }
//        echo $ErrorMsg;
        $this->TPL->ErrorMsg = $ErrorMsg;
        $this->TPL->content = $this->TPL->execute('FormRegistre.html');
        return $this->Generate($this->TPL);
        
    }



}
