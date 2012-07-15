<?php
/*************************************************
* Инициализация приложения
* autor: Frolov Aleksandr
* mail: <infectiveip@gmail.com>
* www:  <frados.org>
* 
*************************************************/

$site_path = realpath(dirname(__FILE__).DIRSEP."..".DIRSEP).DIRSEP;
define('SITEPATH', $site_path);

// Загрузка классов «на лету»
function __autoload($class_name) {

    $filename = 'class.'.strtolower($class_name).'.php';

    $file = SITEPATH.'application'.DIRSEP.'classes'.DIRSEP.$filename;

    if(file_exists($file)==false){
        return false;
    }

    include_once $file;
}

$options = false;
//Читаем congig.php
require_once (SITEPATH.'application'.DIRSEP.'config'.DIRSEP.'config.php');

//Устанавливаем настройки
App::Main()->set("options",$options);

//Запуск приложения
App::Main()->Run();