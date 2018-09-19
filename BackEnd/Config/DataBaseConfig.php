<?php defined('ROOT') OR die('No direct script access.');
/**
 * возможные значения db_driver
 * 
 *      MariaDB
 *      PostgreSQL
 *      SQLite
 *      CUBRID
 * 
 * подробнее смотрите описание 
 * https://redbeanphp.com/index.php?p=/connection
 * 
 */
return array(
    'db_driver' => 'MariaDB',
    'db_host' => 'localhost',
    'db_port' => '',
    
    'db_name' => 'ipcampanel',
    'db_login' => 'root',
    'db_pass' => '',
);