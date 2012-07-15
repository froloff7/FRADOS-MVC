<?php
/*************************************************
 * Класс для ядра приложения
 * autor: Frolov Aleksandr
 * mail: <infectiveip@gmail.com>
 * www:  <frados.org>
 * 
 *************************************************/
class App{
    
    private static $instance = null;
    private $vars = array();

    private final function __clone(){}
    private final function __construct(){}
    
    
    /** 
    * @static 
    * @return App 
    */ 
    public static function Main(){
        
        if (null === self::$instance)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /* Запуск контроллера и вывод в браузер результатов его работы      *******/
    public function Run(){
        
        $routeInfo = $this->getController();

        // Файл доступен?
        if (!is_readable($routeInfo["file"])){
            $this->get404Error();
        }

        // Подключаем файл
        include($routeInfo["file"]);

        // Создаём экземпляр контроллера
        $controller = new $routeInfo["controller"]();
        $this->set("controller",$controller);

        // Действие доступно?
        if(!is_callable(array($controller,$routeInfo["action"]))){
            $this->get404Error();
        }

        // Выполняем действие
        $this->get("controller")->{$routeInfo["action"]}();
        
        echo $this->get("result");
    }

    /* Установка значения в <<хранилище данных>>                    ***********/
    public function set($key, $var){
    
        if (isset($this->vars[$key])){
                throw new Exception('Unable to set var `' . $key . '`. Already set.');
        }
        
        $this->vars[$key] = $var;
        return true;
    }

    /* Получение значения из <<хранилища данных>>                   ***********/
    public function get($key){
    
        if (isset($this->vars[$key]) == false) {
                return null;
        }

        return $this->vars[$key];
    }

    /* Удаление значения из <<хранилища данных>>                    ***********/
    public function remove($key) {
    
        unset($this->vars[$key]);
    }

    /* Запись данных из VIEW в <<хранилище данных>> с ключем `result`   *******/
    public function getView($name,$data){

        $vpath = SITEPATH.'application'.DIRSEP.'views'.DIRSEP;
        
        // Проверяем файл отображения
        if (!is_readable($vpath.$name.'.php')){
            $this->getFileError($vpath.$name.'.php');
        }

        ob_start();
        include ($vpath.$name.'.php');
        $view = $this->get('result').ob_get_clean();
        $this->remove('result');
        $this->set('result',$view);
        
    }
    
    /* Возврат данных из VIEW без записи в <<хранилище данных>>         *******/
    public function getViewResult($name,$data){

        $vpath = SITEPATH.'application'.DIRSEP.'views'.DIRSEP;
        
        // Проверяем файл отображения
        if (!is_readable($vpath.$name.'.php')){
            $this->getFileError($vpath.$name.'.php');
        }

        ob_start();
        include ($vpath.$name.'.php');
        $view = ob_get_clean();
        return $view;        
    }
    
    /* Инициализация и возврат модели с записью в <<хранилище данных>>  *******/
    public function getModel($name){

        $mpath = SITEPATH.'application'.DIRSEP.'models'.DIRSEP;
        $mname = "Model_$name";
        // Файл доступен?
        if(!is_readable($mpath.$name.'.php')){
            $this->getFileError($mpath.$name.'.php');
        }

        include($mpath.$name.'.php');

        // Создаём экземпляр модели
        $model = new $mname();
        $this->set($mname,$model);

        return $this->get($mname);
    }

    /* Анализ строки запроса и возвращение данных о запрашиваемом контроллере */
    private function getController(){

        $route = (empty($_GET['route'])) ? '' : $_GET['route'];
        $cpath = SITEPATH.'application'.DIRSEP.'controllers'.DIRSEP;
        if(empty($route)){$controller = $this->vars["options"]['default_controller'];}

        // Получаем раздельные части
        $route = trim($route, '/\\');
        $parts = explode('/', $route);

        // Получаем контроллер
        if(is_file($cpath.$parts['0'].'.php')){

            $controller = $parts[0];
        }
        if(empty($controller)){$controller = '';}

        // Получаем действие
        if(isset($parts['1'])){$action = $parts['1'];}
        if (empty($action)){$action = 'index';}

        return array(
            "file"          =>  $cpath.$controller.'.php',
            "controller"    =>  "Controller_".$controller,
            "action"        =>  $action,
            "args"          =>  array_slice($parts, 2)            
        );
    }

    /* Вывод 404 ошибки и выход                                     ***********/
    private function get404Error(){
        
        $sapi_name = php_sapi_name();
        if($sapi_name=='cgi'||$sapi_name=='cgi-fcgi'){$header = 'Status: 404 Not Found';}
        else{$header = $_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found';} 
        
        header($header);
        die('404 Not Found');
    }

    /* Вывод сообщения об отсутствии файла и выход                  ***********/
    private function getFileError($file){
        
        die("Error with file `$file`");
    }

}

