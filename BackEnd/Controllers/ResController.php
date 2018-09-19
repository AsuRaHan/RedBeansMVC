<?php defined('ROOT') OR die('No direct script access.');

/**
 * Description of ResController
 *
 * @author AsuRaHan
 */
class ResController extends Controller{
    public function __construct(){
//        parent::__construct();
        // Обязательно включаем кеширование браузером. установленно на сутки но в продакшене можно и больше
        header("Cache-control: public");
        header("Expires: ".gmdate("D, d M Y H:i:s", time()+60*60*24)." GMT");
    }
    public function ActionCss() {
        $path = implode(DIRECTORY_SEPARATOR, func_get_args());
        $path = $this->Config()['App_Templates_Dir'].'css'.DIRECTORY_SEPARATOR.$path ;
        header("Content-type: text/css; charset: UTF-8");
        if(file_exists($path)){
            return file_get_contents($path); 
        } else {
            header("HTTP/1.0 404 Not Found");
        }
    }
    public function ActionJs() {
        $path = implode(DIRECTORY_SEPARATOR, func_get_args());
        $path = $this->Config()['App_Templates_Dir'].'js'.DIRECTORY_SEPARATOR.$path ;
        if (file_exists($path)) {
            $FileTipe = mime_content_type($path);
            header("Content-Type: $FileTipe");
            return file_get_contents($path);
        }else {
            header("HTTP/1.0 404 Not Found");
        }
    }
    public function ActionFont() {
        $path = implode(DIRECTORY_SEPARATOR, func_get_args());
        $path = $this->Config()['App_Templates_Dir'] . 'font' . DIRECTORY_SEPARATOR . $path;
        if (file_exists($path)) {
            $FileTipe = mime_content_type($path);
            header("Content-Type: $FileTipe");
            return file_get_contents($path);
        }else {
            header("HTTP/1.0 404 Not Found");
        }
    }
    public function ActionImg() {
        $path = implode(DIRECTORY_SEPARATOR, func_get_args());
        $path = $this->Config()['App_Templates_Dir'].'img'.DIRECTORY_SEPARATOR.$path ;
        if (file_exists($path)) {
            $FileTipe = mime_content_type($path);
            header("Content-Type: $FileTipe");
            return file_get_contents($path);
        }else {
            header("HTTP/1.0 404 Not Found");
        }
    }
        
}
