<?php defined('ROOT') OR die('No direct script access.');

/**
 * Description of UserModel
 *
 * @author ivank
 */
class UserModel extends Model{
    private $TableName;
    public function __construct() {
        parent::__construct();
        Session::init();
        $this->TableName = strtolower(__CLASS__);
    }
    public function GetCountUser(){
        return $this->count($this->TableName);
    }
    public function GetListUser($start=1,$limit=10,$order=null) {
//        $users = $this->findAndExport($this->TableName,'ORDER BY id LIMIT '.(($page-1)*$limit).', '.$limit);
        if(is_array($order)){
            if($order['search']==''){
                $users = $this->findAll($this->TableName,'ORDER BY '.$order['data'].' '.$order['dir']. ' LIMIT '.$start.', '.$limit);
//                $Tarr = array(
//                    ':order' => $order['data'],
//                    ':dir' => $order['dir'],
//                    ':start' => $start,
//                    ':limit' => $limit
//                );
//                $users = $this->findAll($this->TableName,'ORDER BY :order :dir LIMIT :start, :limit',$Tarr);
            } else {
                $Farr = array('surname' => array($order['search']));
                $users = $this->findLike($this->TableName, $Farr, 'LIMIT '.$start.', '.$limit);
                
            }
            
        } else {
            $users = $this->findAll($this->TableName,'LIMIT '.$start.', '.$limit);
        }
        
        
        if($users){
            
            return $users;
        }
        return FALSE;
    }
    public function GetUser($id) {
        $u = $this->findOne($this->TableName, 'id = ?', array($id));
        if($u){
            return $u->export();
        }
        return FALSE;
    }
    public function DellUser($id=0) {
        $User = $this->load($this->TableName, $id);
        return $this->trash($User);
    }
    public function GetCurrentUser() {
        $var = Session::get('LoggedUser');
//        var_dump($var);
//        die();
        if($var){
            $email = $var['email'];
            $user = $this->findOne($this->TableName, 'email = ?', array($email));
//            var_dump($user);
//            die();
            if($user){
                $ret = $user->export();
                unset($ret["password"]);
                return $ret;
            }
            return FALSE;
        }
        return FALSE;
    }
    public function ChekMail($mail) {
        
        if ($this->count($this->TableName, "email = ?", array($mail)) > 0)
        {
//            $errors[] = 'Пользователь с таким Email уже существует!';
            return TRUE;
        }
        return FALSE;
    }
    
    public function ChekUserLogin($login) {
        //проверка на существование одинакового логина
        if ($this->count($this->TableName, "login = ?", array($login)) > 0) {
//            $errors[] = 'Пользователь с таким логином уже существует!';
            return TRUE;
        }
        return FALSE;
    }
    public function CreateUser($email, $password, $login, $name='', $middlename='', $surname='', $role = 100, $phone='', $registredatetime='') {
        
        $user = $this->dispense($this->TableName);
        $user->name = $name;
        $user->middlename = $middlename;
        $user->surname = $surname;
        
        $user->login = $login;
        $user->email = $email;
        $user->phone = $phone;
        $user->password = password_hash($password, PASSWORD_DEFAULT);
        //пароль нельзя хранить в открытом виде, 
        //мы его шифруем при помощи функции password_hash для php > 5.6
        
        $user->role = $role;
        
        $user->activation = TRUE;
        if($registredatetime == ''){
            $registredatetime  = date('Y-m-d H:i:s');
        }
        $user->registredatetime = $registredatetime;
        
        return $this->store($user);
    }
    public function SaveUser($id, $email, $password, $login, $name, $middlename, $surname, $role = 100, $phone = '') {

//        $user = $this->dispense($this->TableName);
        $user = $this->findOne($this->TableName, 'id = ?', array($id));
        $user->name = $name;
        $user->middlename = $middlename;
        $user->surname = $surname;

        $user->login = $login;
        $user->email = $email;
        $user->phone = $phone;
        //пароль нельзя хранить в открытом виде, 
        //мы его шифруем при помощи функции password_hash для php > 5.6
        if($password<>''){
            $user->password = password_hash($password, PASSWORD_DEFAULT);
        }
        
        $user->role = $role;
        
        return $this->store($user);
    }

    public function login($email, $password) {
        $user = $this->findOne($this->TableName, 'email = ?', array($email));
        
        if ($user) {
            //логин существует
            if (password_verify($password, $user->password)) {
                //если пароль совпадает, то нужно авторизовать пользователя

                $user->lastlogin = date('Y-m-d H:i:s');
                $this->store($user);
                $VarUser = $user->export();
                unset($VarUser['password']); // убираем хеш пароля. безопастность кода залог зарплаты
                Session::set('LoggedUser', $VarUser);
                return TRUE;
            } 
        }
        
        return FALSE;
    }
}
