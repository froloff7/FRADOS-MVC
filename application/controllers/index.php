<?php
/*************************************************
* Контроллер для вывода страницы
* autor: Frolov Aleksandr
* mail: <infectiveip@gmail.com>
* www:  <frados.org>
* 
*************************************************/
class Controller_index extends Controller{
    
    private $user = null;
    
    public function index(){
        
        /*  INIT USER  ********************************************************/
        $this->user = App::Main()->getModel('user');

        /*  GET VIEW   ********************************************************/
        
        App::Main()->getView('main',  $this->user->getName());

    }   
    
}