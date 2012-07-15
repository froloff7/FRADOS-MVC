<?php
/*************************************************
 * Базовый класс для модели
 * autor: Frolov Aleksandr
 * mail: <infectiveip@gmail.com>
 * www:  <frados.org>
 * 
 *************************************************/
Abstract Class Model{

    /** 
    * @return DB
    */ 
    public $db = null;

    function __construct(){}

    protected function initDb(){
        
        $this->db = DB::MySQLi();
    } 

}