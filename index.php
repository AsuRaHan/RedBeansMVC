<?php


//define('TIME_START', microtime(true));// для подсчета времени работы скрипта
//define('USE_MEM', memory_get_usage()); // тоже самое только для используемой памяти сервера
if (version_compare(phpversion(), '5.6.0', '<') == true) {
    die('версия PHP меньше 5.6 продолжить невозможно. обновите версию PHP');
}

define('DS', DIRECTORY_SEPARATOR); // разделитель для путей к файлам
define('ROOT', dirname(__FILE__) . DS); // защита всех файлов приложения от прямого доступа к ним
define('SITE_DIR', realpath(dirname(__FILE__)) . DS); // путь к корневой папке сайта getcwd()
define('APP', SITE_DIR . 'BackEnd' . DS); // путь к приложению
//define('TEMPLATE_DIR', SITE_DIR . 'portal' . DS . 'dist' . DS); // путь до файлов до шаблонами
define('TEMPLATE_DIR', SITE_DIR . 'FrontEnd' . DS);

define('CONFIG_DIR', APP . 'Config' . DS); // папка с конфигами

//define ('WRITE_LOG', TRUE); // вести логирование работы или нет
//require_once SITE_DIR.'vendor'.DS.'autoload.php'; // подключаем композер
require_once APP . 'CORE.php';
CORE::Run();
