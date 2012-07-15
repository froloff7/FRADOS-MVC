<?php
/*************************************************
 * Класс для работы с базой данных MySql ... использует драйвер mysqli
 * autor: Frolov Aleksandr
 * mail: <infectiveip@gmail.com>
 * www:  <frados.org>
 * 
 *************************************************/
class DB{
    
    private static $instance = null;
    private static $DBI = null;
    private static $STMT = null;
    private static $CONFIG = null;
    private final function __clone(){}
    private final function __construct(){}


    /** 
    * @static 
    * @return DB
    */ 
    public static function MySQLi(){
        
        if (null === self::$instance)
        {
            self::InitDbi();
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
    * Init MySQLi connection
    * @return void;
    * @return die();
    */
    private function InitDbi(){
        
        self::$CONFIG = App::Main()->get("options");
        self::$DBI = new mysqli(self::$CONFIG['db_host'],self::$CONFIG['db_user'],self::$CONFIG['db_passwd'],self::$CONFIG['db_base']);
        if (mysqli_connect_errno()){self::InitError("<!--Подключение к серверу MySQL невозможно. Код ошибки:".mysqli_connect_error()."-->");}
        self::$DBI->set_charset("utf8");
    }
    
    /**
    * Bind parametr's to query
    * @param array
    * @return void;
    */
    private function SetParams(&$param){
        
        if(count($param)>1)
        {
            if(strlen($param[0]) != (sizeof($param)-1)){self::InitError("<!--Неверный MySQL запрос...".print_r($param,true)."-->");}
            $refs = array(); 
	    foreach($param as $key => $value) 
	    {
                $refs[$key] = &$param[$key];
            }
            call_user_func_array(array(self::$STMT, 'bind_param'), $refs);
        }
    }
    
    /**
    * Escape all data
    * @param array|string|int
    * @return escaped data
    */
    private function DbiMakeSafe($data){
        
        if(is_array($data)){ 
            foreach ($data as &$item) $item = self::DbiMakeSafe($item); 
        } else { 
            $data = self::$DBI->escape_string($data); 
        } 
        return $data;
    }
    
    /**
    * GET fields name of table in QUERY
    * @return array;
    */
    private function GetColumns(){
        
        if(self::$STMT==null){return false;}
        else{
            $fields = array();
            $f = self::$STMT->result_metadata();
            for($i=0;$i<$f->field_count;$i++)
            {
                $field = $f->fetch_field();
                $fields[] = $field->name;
            }
            return $fields;
        }
    }
    
    private function InitError($str){
        
        die($str);
    }
    
    /**
     * @return array
     * @return null
     */
    public function setQueryInfo(){
        
        if(self::$STMT==null){return null;}
        else{
            $info = array(
                'affected_rows' => self::$STMT->affected_rows,
                'insert_id' => self::$STMT->insert_id,
                'num_rows' => self::$STMT->num_rows,
                'field_count' => self::$STMT->field_count,
                'sqlstate' => self::$STMT->sqlstate,
            );
            return $info;
        }    
    }
    
    /**
     * @return string of error
     * @return ''
     */
    public function DbiLastError(){
        
        return self::$DBI->error;
    }
    
    /**
     * Simple db QUERY DON'T USE 
     * @param string
     * @return link to result resource
     */
    public function DbiQuery($sql){
        
        if(null === self::$DBI){self::InitError("<!--Ошибка инициализации приложения-->");}
        $result = self::$DBI->query($sql);
        return $result;
    }

    /**
     * NONE SELECT QUERY 
     * @param string
     * @param string(format)
     * @param n1    - data1
     * @param n...  - data...
     * @param n - datan
     * @return FALSE OR result
     * 
     * DB::MySQLi()->NoneDbQuery("UPDATE options SET value=? WHERE name=?","ss",$a,$b))
     */
    public function NoneDbQuery(){
        
        $result = False;
        if(null === self::$DBI){self::InitError("<!--Ошибка инициализации приложения-->");}
        if(self::$STMT!=null){return $result;}
        else
        {    
            $args = func_get_args();
            $query = $args[0];
            $param = array_slice($args,1);
            self::$STMT = self::$DBI->prepare($query);
            self::SetParams($param);
            $result = self::$STMT->execute();
            self::$STMT = null;
            return $result;
        }
    }
    
    /**
     * SELECT QUERY 
     * @param string
     * @param string(format)
     * @param n1    - data1
     * @param n...  - data...
     * @param n - datan
     * @return FALSE OR array
     * 
     * DB::MySQLi()->DbQuery("SELECT * FROM options WHERE id=? AND name=?","is",$i,$a))
     */
    public function DbQuery(){
        
        $result = False;
        if(null === self::$DBI){self::InitError("<!--Ошибка инициализации приложения-->");}
        if(self::$STMT!=null){return $result;}
        else
        {    
            $args = func_get_args();
            $query = $args[0];
            $param = array_slice($args,1);
            self::$STMT = self::$DBI->prepare($query);
            self::SetParams($param);
            $res = self::$STMT->execute();
            if($res)
            {                                                   
                $fields = $this->GetColumns();
                $bind_r = array();
                foreach ($fields as $field)
                {    
                    $bind_r[] = &${$field};
                }    
                call_user_func_array(array(self::$STMT,"bind_result"),$bind_r);
                $i=0;
                while(self::$STMT->fetch())
                {
                    foreach ($fields as $field) 
                    { 
                        $result[$i][$field] = $$field;
                    }
                    $i++;
                }
                self::$STMT = null;
                return $result;
            }
            else
            {
                self::$STMT = null;
                return $result;
            }
        }
    }
    
    /**
     * SELECT QUERY RETURN 1 ROW
     * @param string
     * @param string(format)
     * @param n1    - data1
     * @param n...  - data...
     * @param n - datan
     * @return FALSE OR array
     * 
     * DB::MySQLi()->DbFirst("SELECT * FROM options WHERE name=? OR id=? LIMIT 1","si",$a,$i))
     */
    public function DbFirst(){
        
        $result = False;
        if(null === self::$DBI){self::InitError("<!--Ошибка инициализации приложения-->");}
        if(self::$STMT!=null){return $result;}
        else
        {    
            $args = func_get_args();
            $query = $args[0];
            $param = array_slice($args,1);
            self::$STMT = self::$DBI->prepare($query);
            self::SetParams($param);
            $res = self::$STMT->execute();
            if($res)
            {                                                   
                $fields = $this->GetColumns();
                $bind_r = array();
                foreach ($fields as $field)
                {    
                    $bind_r[] = &${$field};
                }    
                call_user_func_array(array(self::$STMT,"bind_result"),$bind_r);
                if(self::$STMT->fetch())
                {
                    foreach ($fields as $field) 
                    { 
                        $result[$field] = $$field;
                    }
                }    
                self::$STMT = null;
                return $result;
            }
            else
            {
                self::$STMT = null;
                return $result;
            }
        }
    }
    
    /**
     * SELECT QUERY RETURN 1 field
     * @param string
     * @param string(format)
     * @param n1    - data1
     * @param n...  - data...
     * @param n - datan
     * @return FALSE OR field_value
     * 
     * $id = DB::MySQLi()->DbKey("SELECT id FROM options WHERE name=? LIMIT 1","s",$a))
     */
    public function DbKey(){
        
        $result = False;
        if(null === self::$DBI){self::InitError("<!--Ошибка инициализации приложения-->");}
        if(self::$STMT!=null){return $result;}
        else
        {    
            $args = func_get_args();
            $query = $args[0];
            $param = array_slice($args,1);
            self::$STMT = self::$DBI->prepare($query);
            self::SetParams($param);
            $res = self::$STMT->execute();
            if($res)
            {                                                   
                self::$STMT->bind_result($result);
                self::$STMT->fetch();
            }
            self::$STMT = null;
            return $result;
        }
    }
    
    /**
     * INSERT QUERY
     * @param string
     * @param string(format)
     * @param n1    - data1
     * @param n...  - data...
     * @param n - datan
     * @return FALSE OR inserted_id
     * 
     * $id = DB::MySQLi()->DbArrayInsert("options","sssi",array("id"=>"","name"=>$a,"value"=>$b,"timeint"=>time()))
    */
    public function DbArrayInsert($table,$safe,$data){
        
        if(strlen($safe) != sizeof($data)){$result = 0;}
        else
        {
            if(null === self::$DBI){self::InitError("<!--Ошибка инициализации приложения-->");}
            $query = "INSERT INTO $table (";
            $k = "";
            foreach ($data as $key => $value)
            {
                $query .= $k.self::DbiMakeSafe($key);
                $k = ",";
            }
            $query .= ") VALUES (?";
            $param[] = $safe;
            for($i=1;$i<sizeof($data);$i++)
            {
                $query .= ',?';
            }
            $query .= ");";
            foreach ($data as $key => $value)
            {
                $param[] = $value;
            }
            if(self::$STMT!=null){return $result;}
            else
            {    
                self::$STMT = self::$DBI->prepare($query);
                self::SetParams($param);
                $res = self::$STMT->execute();
                if($res)
                {
                    $result = self::setQueryInfo();
                    $result = $result['insert_id'];
                }
                else{$result = 0;}
                            
                self::$STMT = null;
                return $result;
            }    
        }
    }
    
    /**
    * Close connection
    * @return void;
    */
    public function DbiClose(){
        
        self::$DBI->close();
        self::$STMT =   null;
        self::$DBI  =   null;
    }
    
    /**
    * Return salt
    * @return salt OR FALSE;
    */
    public function GetSalt(){
        
        if(isset(self::$CONFIG['salt'])){
            return self::$CONFIG['salt'];
        }
        else{return false;}
    }
}