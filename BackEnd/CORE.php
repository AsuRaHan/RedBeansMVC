<?php defined('ROOT') OR die('No direct script access.');
/**
 * Главный клас всего приложения
 *
 */
function translit($str) {
    $rus = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
    $lat = array('A', 'B', 'V', 'G', 'D', 'E', 'E', 'Gh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Sch', 'Y', 'Y', 'Y', 'E', 'Yu', 'Ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya');
    return str_replace($rus, $lat, $str);
}

class CORE {
    public static $ErrorMessage;
    
    Private static $globalConfig = [];
    private static $ExecRetVal;
    private static $ExecOK = false;
    /*
     * Переменные роутинга 
     */
    Private static $URI = ''; // Строка УРЛ запроса  site.com/Controller/Action/Param1/Param2/Param3/ и так далее
    Private static $ControllerName; // Имя выполняемого контроллера <Controller>
    Private static $ActionName; // Имя выполняемого метода <Action>
    Private static $ControllerFile; // подключаемый фаил контроллера <...\ControllerPath\*Name*Controller.php>
    Private static $ParametersArray; // массив параметров которые пришли в УРЛ строке
    
    Private static $OldErrorHandler;
    public static function ErrorMessage(){return self::$ErrorMessage;}
    public static function Config(){return self::$globalConfig;}

    public static function Run() {
        self::SetupConfig();
        
        spl_autoload_register(__CLASS__ . '::AutoLoadClassFile');
        // говорим php отслеживать все возможные ошибки
//        ini_set('display_errors', 'on');
        ini_set('max_execution_time', 900);
        error_reporting(E_ALL | E_STRICT);
        // после завершения сработает этот метод и выдаст все необходимое в браузер 
        register_shutdown_function(__CLASS__ . '::ShutDown');
        
        self::$OldErrorHandler = set_error_handler(__CLASS__ . '::ErrorHandler');
        // регистрируем свой обработчик выброшенных исключений
        if (version_compare(phpversion(), '7.0.0', '<') == true) {
            set_exception_handler(__CLASS__ . '::ExceptionHandler5');
        } else {
            set_exception_handler(__CLASS__ . '::ExceptionHandler7');
        }
        
        
        if (!self::GetControllerAndAction()) {
//            header("HTTP/1.1 404 Not Found");
//            header("Status: 404 Not Found");
            self::$ExecRetVal = __METHOD__ . ' [ERROR:404] фаил Контроллера <b style="color: red;">' . self::$ControllerName . '.php</b> не найден<br>';
            self::$ExecOK = TRUE;
//            throw new Exception(__METHOD__ . ' [ERROR:404] фаил Контроллера <b style="color: red;">' . self::$ControllerName . '.php</b> не найден<br>');
            return FALSE;
        }
       
//        self::$ExecRetVal = 'app start';
        self::$ExecRetVal = self::Exec(self::$ControllerName, self::$ActionName, self::$ParametersArray);
        
        self::$ExecOK = TRUE;
        return self::$ExecOK; // если мы здесь то приложение отработало и метод ShotDown() все отобразит
//        return self::$ErrorMessage;
       
        
    }
    public static function Exec($Controller ='' ,$Action='',$Param=[]) {
        
        $ctrl = $Controller;
        $action = $Action;
        if (class_exists($ctrl)) {
            $objectCtrl = new $ctrl();
        }
        if (!method_exists($objectCtrl, $action)) {
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            return __METHOD__ . " Контроллер <b style=\"color: red;\">$ctrl</b> не имеет метода <b style=\"color: red;\">$action</b>";
//            return FALSE;
        }
        if (count($Param)) {
            return call_user_func_array(array($objectCtrl, $action), $Param);
        } else {
            return call_user_func(array($objectCtrl, $action));
        }
    }
    /**
     * функция получения запроса который пришел от пользователя приложением
     * @return String
     */
    private static function GetURI() {

        $pathInfo = filter_input(INPUT_SERVER, 'PATH_INFO');

        if ($pathInfo) {
            $path = $pathInfo;
        } else {
            $requestURI = filter_input(INPUT_SERVER, 'REQUEST_URI');
//            var_dump($requestURI);
            if (strpos($requestURI, '?') !== false) {
                $requestURI = substr($requestURI, 0, strpos($requestURI, '?'));
            }
            $base = filter_input(INPUT_SERVER, 'BASE');
            if ($base) {
                $path = substr($requestURI, strlen($base));
            } else {
                $path = $requestURI;
            }
        }

        $path = trim($path);
        if (!$path) {
            $path = '/';
        }
//        var_dump($requestURI);
        $path = parse_url($path);
        self::$URI = trim($path['path'], '/');
        return self::$URI;
    }
    /**
     * Получаем контроллер и метод.
     * данная функция находит в УРЛ тот контроллер и метот на который пршол запрос
     * заполныет переменные роутинга
     * и если необходимо настраевает пришедший в УРЛ запрос
     * @return Boolean
     */
    private static function GetControllerAndAction() {
        $access = false;
        self::GetURI();
        $routes = include(self::$globalConfig['App_Config_Dir'].DIRECTORY_SEPARATOR.self::$globalConfig['App_Router_Config_File']);
//        var_dump($routes);
        // проверяю запрос на соответствие регулярному выражению
        foreach ($routes as $uriPattern => $path) {
            if (!preg_match("~$uriPattern~", self::$URI)) {
                continue;
            }
            // получаем внутренний путь из внешнего согласно правилам маршрутизации
            $access = preg_replace("~$uriPattern~", $path, self::$URI);
        }

        if (!$access) {
            if (empty(self::$URI)) {
                $access = self::$globalConfig['Router_Default_Controller'] . "/" . self::$globalConfig['Router_Default_Action']; //
            } else {
                $access = self::$URI;
            }
        }
//        var_dump($access);
        $segments = explode('/', $access);

        $dirForControllers = self::$globalConfig['App_Controllers_Dir'];
        $controlerName = ucfirst(array_shift($segments) . 'Controller');
        self::$ControllerName = $controlerName;

        $controllerFile = $dirForControllers . $controlerName . '.php';


        $action = ucfirst(array_shift($segments));

        if (empty($action)) {
            $action = ucfirst(self::$globalConfig['Router_Default_Action']);
//            var_dump($this->globalConfig['Router_Default_Action']);
        }
        $actionName = 'Action' . $action;
        self::$ActionName = $actionName;

        self::$ParametersArray = $segments;

        if (!file_exists($controllerFile)) {
//            if (file_exists($dirForControllers) && is_dir($dirForControllers)) {
//                die('Директория с контроллерами не найден');
//            }
            $dirArray = scandir($dirForControllers);
            foreach ($dirArray as $da) {
//                echo $da;
                if (is_dir($dirForControllers . $da)) {

                    $controllerFile = $dirForControllers . $da . DIRECTORY_SEPARATOR . $controlerName . '.php';

                    if (file_exists($controllerFile)) { // если в текущей подпапке есть контроллер
                        self::$ControllerFile = $controllerFile;
                        return $controllerFile; // и выходим из функции
                    }
                }
//                echo $da . '<br>';
            }
            
//            header('HTTP/1.0 404 Not Found');
//            exit('Нет контроллера '.$controlerName);
//            throw new Exception(__METHOD__ . ' [ERROR:404] фаил Контроллера <b style="color: red;">' . $controlerName . '.php</b> не найден<br>');
            return FALSE;
        } else {
            self::$ControllerFile = $controllerFile;
            return $controllerFile;
        }
//        require_once ($controllerFile);
//        throw new Exception(__METHOD__ . ' [ERROR:404] фаил Контроллера <b style="color: red;">' . $controlerName . '.php</b> не найден<br>');
        return FALSE;
    }
    /**
     * настраиваем основную конфигурацию ядра системы
     */
    private static function SetupConfig(){
        self::$globalConfig['App_Name'] = 'SSOKRN';
        self::$globalConfig['App_Dir'] = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
        self::$globalConfig['App_Classes_Dir'] = self::$globalConfig['App_Dir'] . 'Classes' . DIRECTORY_SEPARATOR;
        self::$globalConfig['App_Config_Dir'] = self::$globalConfig['App_Dir'] . 'Config' . DIRECTORY_SEPARATOR;
        self::$globalConfig['App_Controllers_Dir'] = self::$globalConfig['App_Dir'] . 'Controllers' . DIRECTORY_SEPARATOR;
        self::$globalConfig['App_Models_Dir'] = self::$globalConfig['App_Dir'] . 'Models' . DIRECTORY_SEPARATOR;
        
//        $accept_languages = filter_input(INPUT_SERVER, "HTTP_ACCEPT_LANGUAGE", FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
//        $locale = locale_accept_from_http($accept_languages);
//        echo $locale;
        self::$globalConfig['App_Templates_Dir'] = TEMPLATE_DIR;// . 'default' . DIRECTORY_SEPARATOR;
        
        if (!file_exists(self::$globalConfig['App_Config_Dir'] . 'AutoLoaderConfig.php')) {
//            exit('AutoLoaderConfig.php не найден');
            self::$globalConfig['App_Clas_Loader_Dir_Array'] = array('Classes','Models','Controllers');
        } else {
            self::$globalConfig['App_Clas_Loader_Dir_Array'] = include_once self::$globalConfig['App_Config_Dir'] . 'AutoLoaderConfig.php';
        }
        
        self::$globalConfig['App_Router_Config_File'] = 'RoutesConfig.php';
        self::$globalConfig['Router_Default_Controller'] = 'index';
        self::$globalConfig['Router_Default_Action'] = 'index';
//        $str_value = serialize(self::$globalConfig);
//        echo $str_value;
    }
    
    /**
     * Автоматическая загрузка классов
     * @param type $className
     * @return boolean
     */
    private static function AutoLoadClassFile($className) {
        //echo $className. '<br>';
        $classFile = self::$globalConfig['App_Classes_Dir'] . $className . '.php';
        if (file_exists($classFile)) {
            include_once $classFile;
            return $classFile;
        }
        return self::LoadClassFileForAllDir($className);
    }

    private static function LoadClassFileForAllDir($className) {
//        echo $className;
//        self::$loadClassArray[] = $className;
        $dirArr = self::$globalConfig['App_Clas_Loader_Dir_Array'];
        $appDir = self::$globalConfig['App_Dir'];
        foreach ($dirArr as $value) {

            $classFile = self::SearchFile($className . '.php', $appDir . $value);

            if (file_exists($classFile)) {
                include_once $classFile;
                return $classFile;
            }
        }
        return FALSE;
    }

    /**
     * Поиск файла по имени во всех папках и подпапках
     * @param string $fileName - искомый файл
     * @param string $folderName - пусть до папки
     */
    public static function SearchFile($fileName, $folderName) {
        // перебираем пока есть файлы
        $dirArray = scandir($folderName);

        foreach ($dirArray as $file) {

            if ($file != "." && $file != "..") {

                // если файл проверяем имя
                if (is_file($folderName . DIRECTORY_SEPARATOR . $file)) {
                    // если имя файла искомое,
                    // то вернем путь до него
                    if ($file == $fileName) {
                        return $folderName . DIRECTORY_SEPARATOR . $file;
                    }
//                    echo $folderName.'\\'.$file.'<br>';
                }
                // если папка, то рекурсивно
                // вызываем SearchFile
                if (is_dir($folderName . DIRECTORY_SEPARATOR . $file)) {
                    $retVal = self::SearchFile($fileName, $folderName . DIRECTORY_SEPARATOR . $file);
                    if ($retVal) { // если фуекция что-то вернула то выходим
                        return $retVal;
                    }
                }
            }
        }
    }

    public static function ErrorHandler($errno, $errstr, $errfile, $errline) {
        self::GenerateErrorMessage($errno, $errstr, $errfile, $errline);
        return TRUE;
    }
    /**
     * Метод, который будет обрабатывать исключения,
     * вызывается если версмя PHP меньше 7
     * вызванные вне блока try/catch
     *
     * @param \Exception $e
     */
    public static function ExceptionHandler5(\Exception $e) {
        
        echo '<pre>';
        var_dump($e);
        echo '</pre>';
    }
    /**
     * Метод, который будет обрабатывать исключения,
     * вызывается для PHP 7 версии и выше
     * вызванные вне блока try/catch
     *
     * @param \Throwable $t
     */
    public static function ExceptionHandler7(\Throwable $t) {
        $trace = str_replace(SITE_DIR, '...' . DS, $t->getTraceAsString());
        
        self::$ErrorMessage .='<div style="border: 1px solid rgb(170, 170, 170); z-index: 9999;">';
        self::GenerateErrorMessage($t->getCode(), $t->getMessage(), $t->getFile(), $t->getLine(),$trace);
//        $trace = $t->getTraceAsString();
        
        self::$ErrorMessage .="</div>";
   
//        echo self::$ErrorMessage;
//        echo '<pre>';
//        var_dump($trace);
//        echo '</pre>';
    }
    /**
     * 
     * Метод который завершает все приложение и проверяет наличие ошибок
     * 
     */
    public static function ShutDown(){
        $error = error_get_last();
        if($error){ // если есть ошибки
            $errno = $error["type"];
            $errstr = $error["message"];
            $errfile = $error["file"];
            $errline = $error["line"];
            self::GenerateErrorMessage($errno, $errstr, $errfile, $errline);
        } 
        if (self::$ExecOK) { // но приложение отработало до конца
            if(self::$ErrorMessage){
                echo self::$ErrorMessage;
                echo self::$ExecRetVal;
                die();
            }
            echo self::$ExecRetVal; // то выводим ...
        } else { // иначе
            echo self::$ErrorMessage; // выводим сообщение об ошибках
        }
    }
    private static function GenerateErrorMessage($errno, $errstr, $errfile, $errline,$tras=''){
        $errfile = str_replace(SITE_DIR, '...' . DS, $errfile);
        // временная метка возникновения ошибки
        $dt = date("d-m-Y H:i:s T");

        // определим ассоциативный массив соответствия всех
        // констант уровней ошибок с их названиями, хотя
        // в действительности мы будем рассматривать только
        // следующие типы: E_WARNING, E_NOTICE, E_USER_ERROR,
        // E_USER_WARNING и E_USER_NOTICE
        $errortype = array(
            0 => 'Пользовательское исключение сгенерированное вручную',
            1045 => 'Connect Error (1045) Access denied to DataBase',
            E_ERROR => 'E_ERROR - Фатальные ошибки времени выполнения. Это неустранимые средствами самого скрипта ошибки, такие как ошибка распределения памяти и т.п. Выполнение скрипта в таком случае прекращается.',
            E_WARNING => 'E_WARNING - Предупреждения времени выполнения (не фатальные ошибки). Выполнение скрипта в таком случае не прекращается.',
            E_PARSE => 'E_PARSE - Ошибки на этапе компиляции. Должны генерироваться только парсером. Ошибка разбора исходного кода',
            E_NOTICE => 'E_NOTICE - Уведомления времени выполнения. Указывают на то, что во время выполнения скрипта произошло что-то, что может указывать на ошибку, хотя это может происходить и при обычном выполнении программы.',
            E_CORE_ERROR => 'E_CORE_ERROR - Фатальные ошибки, которые происходят во время запуска РНР. Такие ошибки схожи с E_ERROR, за исключением того, что они генерируются ядром PHP.',
            E_CORE_WARNING => 'E_CORE_WARNING -	Предупреждения (не фатальные ошибки), которые происходят во время начального запуска РНР. Такие предупреждения схожи с E_WARNING, за исключением того, что они генерируются ядром PHP.',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR - Фатальные ошибки на этапе компиляции. Такие ошибки схожи с E_ERROR, за исключением того, что они генерируются скриптовым движком Zend.',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING - Предупреждения на этапе компиляции (не фатальные ошибки). Такие предупреждения схожи с E_WARNING, за исключением того, что они генерируются скриптовым движком Zend.',
            E_USER_ERROR => 'E_USER_ERROR - Сообщения об ошибках, сгенерированные пользователем. Такие ошибки схожи с E_ERROR, за исключением того, что они генерируются в коде скрипта средствами функции PHP trigger_error().',
            E_USER_WARNING => 'E_USER_WARNING -	Предупреждения, сгенерированные пользователем. Такие предупреждения схожи с E_WARNING, за исключением того, что они генерируются в коде скрипта средствами функции PHP trigger_error().',
            E_USER_NOTICE => 'E_USER_NOTICE - Уведомления, сгенерированные пользователем. Такие уведомления схожи с E_NOTICE, за исключением того, что они генерируются в коде скрипта, средствами функции PHP trigger_error().',
            E_STRICT => 'E_STRICT - Включаются для того, чтобы PHP предлагал изменения в коде, которые обеспечат лучшее взаимодействие и совместимость кода.',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR - Фатальные ошибки с возможностью обработки. Такие ошибки указывают, что, вероятно, возникла опасная ситуация, но при этом, скриптовый движок остается в стабильном состоянии. Если такая ошибка не обрабатывается функцией, определенной пользователем для обработки ошибок (см. set_error_handler()), выполнение приложения прерывается, как происходит при ошибках E_ERROR.',
            E_DEPRECATED => 'E_DEPRECATED - Уведомления времени выполнения об использовании устаревших конструкций. Включаются для того, чтобы получать предупреждения о коде, который не будет работать в следующих версиях PHP.',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED - Уведомления времени выполнения об использовании устаревших конструкций, сгенерированные пользователем. Такие уведомления схожи с E_DEPRECATED за исключением того, что они генерируются в коде скрипта, с помощью функции PHP trigger_error().',
            E_ALL => 'E_ALL - Все поддерживаемые ошибки и предупреждения, за исключением ошибок E_STRICT до PHP 5.4.0.'
        );
        if(array_key_exists($errno,$errortype)){
            $ET = $errortype[$errno];
        } else {
            $ET = 'Неизвестная никому ошибка ';
        }
        
        
        $ErrorMsg = '';
//        $ErrorMsg .= "<div>";
//        $ErrorMsg .= "<b style='color: rgb(190, 50, 50);'>$dt</b><br>";
        $ErrorMsg .= "<table>";
        $ErrorMsg .= "<tr style='background-color:rgb(230,230,230);'><th>$dt</th><td>" . $ET . ' №'.$errno."</td></tr>";
        $ErrorMsg .= "<tr style='background-color:rgb(240,240,240);'><th>Сообщение</th><td>{$errstr}</td></tr>";
        $ErrorMsg .= "<tr style='background-color:rgb(230,230,230);'><th>Фаил</th><td>{$errfile}</td></tr>";
        $ErrorMsg .= "<tr style='background-color:rgb(240,240,240);'><th>Строка</th><td>{$errline}</td></tr>";
        $ErrorMsg .= "<tr style='background-color:rgb(200,200,200);'><th>Стек выполнения</th><td><pre>{$tras}</pre></td></tr>";
        $ErrorMsg .= "</table>";
//        restore_error_handler();
//        exit($ErrorMsg);
        self::$ErrorMessage .= $ErrorMsg;
    }
    
}