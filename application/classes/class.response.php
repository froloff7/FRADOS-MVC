<?php
/*************************************************
 * Класс для построения ответов для javascript
 * autor: Frolov Aleksandr
 * mail: <infectiveip@gmail.com>
 * www:  <frados.org>
 * 
 *************************************************/
class Response{

    private $r = array(
      "error"       =>  false,
      "ok"          =>  false,
      "err_string"  =>  "",
      "data"        =>  null        
    );
    
    /**
     * RETURN RESULT
     * @return json
     * 
     */
    function get(){
        
        return json_encode($this->r);
    }
    
    /**
     * INIT ERROR
     * @param string
     */
    function setError($str){
        
        $this->r["error"] = true;
        $this->r["err_string"] = $str;
    }

    /**
     * INIT SUCCESS
     * @param string|array|object|any
     */
    function setSuccess($data){
        
        $this->r["ok"] = true;
        $this->r["data"] = $data;
    }

     /**
     * SET DATA
     * @param string|array|object|any
     */
    function setData($data){
        
        $this->r["data"] = $data;
    }
}
