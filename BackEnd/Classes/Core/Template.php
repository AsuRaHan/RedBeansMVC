<?php

defined('ROOT') OR die('No direct script access.');

/**
 * Класс для работы с шаблонами
 * wive
 * 
 */
class Template {

    private $vars = array();
    public $WivesPath = TEMPLATE_DIR;

    public function __set($name, $value) {
        $this->vars[$name] = $value;
//        var_dump($this->vars);
    }

    public function __get($name) {
        if (isset($this->vars[$name])) {

            return $this->vars[$name];
        }
//        return array();
        return FALSE;
    }

    public function VarGet($name) {
        if (isset($this->vars[$name])) {

            return $this->vars[$name];
        }
        return FALSE;
    }
    public function VarSetArray($Array) {
        if(is_array($Array)){
            foreach ($Array as $key => $value) {
                $this->vars[$key] = $value;
            }
        }
        
    }
    public function __isset($name) {
        if (isset($this->vars[$name]) && !empty($this->vars[$name])) {
            return true;
        }
        return false;
    }

    public function assign($name, $value) {
        if (isset($this->vars[$name]) && is_array($this->vars[$name])) {
            $this->vars[$name] = array_merge($this->vars[$name], (array) $value);
        } else {
            $this->vars[$name] = $value;
        }
    }
    public function compress($code) {
        return  preg_replace(array( '/<!--(.*)-->/Uis','#/\*(?:[^*]*(?:\*(?!/))*)*\*/#','/[\s]+/'), ' ', $code); // '/<!--(.*)-->/Uis','\<![ \r\n\t]*(--([^\-]|[\r\n]|-[^\-])*--[ \r\n\t]*)\>','/[\s]+/'  |,'#/\*(?:[^*]*(?:\*(?!/))*)*\*/#'|
        
    }
    public function execute($path) {
        if (!file_exists($this->WivesPath . $path)) {
            $code = '<p><b>Error: </b>'.__METHOD__. "('$path')</p>Нет файла <strong>$path</strong> для подключения в <b>$this->WivesPath</b>";
            return $code;
        }
        ob_start();
        include $this->WivesPath . $path;
        $code = ob_get_contents();
        ob_end_clean();
//        return $code;
        $code = $this->Code($code);
        $code = $this->compress($code);
//        echo $code;
        return $code;
    }

    public function Code($code) {
        preg_match_all('/{>(.*?)<}/', $code, $subject, PREG_SET_ORDER);
        foreach ($subject as $value) {
            $tplVar = $this->VarGet($value[1]);
//                var_dump($subject);
            if($tplVar){
                $code = preg_replace("/{>($value[1])<}/", $tplVar, $code);
            }
            

//                $code=preg_replace( '/{{(.*?)}}/', $tplVar, $code);
        }
//        die();
        return $code;
    }

    public function SetWivePath($path) {
        $this->WivesPath = $path;
//        echo $path;
    }

}
