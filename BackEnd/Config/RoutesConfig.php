<?php defined('ROOT') OR die('No direct script access.');

return array(
    'post' => 'index/post/',
    'post/([-_a-z0-9]+)' => 'index/post/$1/',
    'api' => 'index/api/',
    'api/([-_a-z0-9]+)' => 'index/api/$1/',
    
    'font' => 'res/font/',
    'font/([-_a-z0-9]+)' => 'res/font/$1',
    'css' => 'res/css/',
    'css/([-_a-z0-9]+)' => 'res/css/$1',
    'js' => 'res/js/',
    'js/([-_a-z0-9]+)' => 'res/js/$1',
    'img' => 'res/img/',
    'img/([-_a-z0-9]+)' => 'res/img/$1'

);
