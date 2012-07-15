<?php
/*************************************************
 * Базовый класс для Контроллера
 * autor: Frolov Aleksandr
 * mail: <infectiveip@gmail.com>
 * www:  <frados.org>
 *************************************************/
Abstract Class Controller{

    protected $post     = false;
    protected $session  = false;
    protected $cookie   = false;
    
    public function __construct(){
        if(sizeof($_POST)>0){$this->post = $_POST;}
        if(sizeof($_COOKIE)>0){$this->cookie = $_COOKIE;}
        if(isset($_SESSION) AND sizeof($_SESSION)>0){$this->session = $_SESSION;}
    }

    abstract function index();

}
