<?php defined('ROOT') OR die('No direct script access.');
/**
 * Description of Model
 *
 * @author ivank
 */
//use RedBeanPHP\Facade as R;
class Model extends R{
    public function __construct() {
        $IncFile = CORE::Config()['App_Config_Dir'] . 'DataBaseConfig.php';
        
        if(file_exists($IncFile)){
            $Config = include_once($IncFile);
        } else {
            throw new Exception(__METHOD__ . " ошибка бвзы данных. неудалось найти фаил конфигурации $IncFile");
        }
//        var_dump($Config);
        $host = $Config['db_host'];
        $port = $Config['db_port'];
        $dbname = $Config['db_name'];
        $login = $Config['db_login'];
        $pass = $Config['db_pass'];
        
        switch ($Config['db_driver']) {
            case "MariaDB":
                $this->setup( "mysql:host=$host;dbname=$dbname",$login, $pass );
                break;
            case "PostgreSQL":
                $this->setup( "pgsql:host=$host;dbname=$dbname",$login, $pass );
                break;
            case "SQLite":
                $this->setup( 'sqlite:'.APP.'database.db' );
                break;
            case "CUBRID":
                $this->setup( "cubrid:host=$host;port=$port;dbname=$dbname",$login,$pass );
                break;
        }
        
        
        if(!$this->testConnection()){
//            $this->fancyDebug( TRUE );
            throw new Exception(__METHOD__ . " ошибка бвзы данных. неудалось установить соединение c БД");
        }
        return $this;
    }
    public function __destruct() {
        $this->close();
    }
}
