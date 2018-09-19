<?php

defined('ROOT') OR die('No direct script access.');
/**
 * Description of Controller
 *
 * @author veles
 */
abstract class Controller extends CORE{
    public $AJAX=FALSE;


    public function __construct(){
        $this->AJAX = filter_input(INPUT_GET, "AJAX");
//        if ($this->AJAX) {
//            sleep(500);
//        }
        return $this;
    }
    
    public function Generate($TPL) {
        if ($this->AJAX) {
            $arr = array(
                'content' => $TPL->content,
                'title' => $TPL->title,
                'ErrorMessage' => $this->ErrorMessage()
            );
            return json_encode($arr);
        }
        $path = $this->Config()['App_Templates_Dir'];  
//        $controlerName = ucfirst($this->Config()['Router_Default_Controller']). 'Controller';
//        $path = $path.$controlerName.DS;
//        exit();
        $TPL->SetWivePath($path);
//        var_dump($TPL);
//        die();
        return $TPL->execute('index.html');
       
    }

}
