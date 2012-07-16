<?php
/*************************************************
* Модель для работы с данными пользователя
* autor: Frolov Aleksandr
* mail: <infectiveip@gmail.com>
* www:  <frados.org>
* 
*************************************************/
class Model_user extends Model{
    
    private  $user = null;
    
    public function __construct(){
        
        //$this->initDb();
    }

    /* Возвращаем имя пользователя      ***************************************/
    public function getName(){
        
        return 'Guest';        
        
        $this->user = $this->db->DbKey("SELECT name FROM user WHERE skey=?","s",$cookie['name']);
    }
   
}