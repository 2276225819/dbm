<?php namespace dbm;

class Connect{ 
    public static $debug=false;
    public static $currect;
    public $attr=[
        \PDO::ATTR_ERRMODE=>\PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_PERSISTENT=>true,
    ];
    public function __construct($dns=null,$name=null,$pass=null){
        static::$currect = $this;
        $this->dns=$dns;
        $this->name=$name;
        $this->pass=$pass;
        $this->reload();
    }  
    public function reload(){
        $this->db = new \PDO($this->dns,$this->name,$this->pass,$this->attr); 
    }
    public function lastInsertId(){
        return $this->db->lastInsertId();
    }
    public function execute($sql,$args = []) { 
        if (isset($this->prefix)) {
            $pf = $this->prefix;
            $sql = preg_replace(
                array('/((?:join|truncate|into|from|create table|alter table|as)\s+)([\w]+)/' ,
                        '/(\w+)\s+(read|write|set)/',  '(\w+\.[\w\*]+)'),
                array("$1 `$pf$2`","`$pf$1` $2" ,"`$pf$0`"), $sql );
        }
        while (true) {
            try {
                if(static::$debug)
                    echo "$sql".print_r($args,true)."\n";
                $query = $this->db->prepare($sql); 
                return $query->execute($args)?$query:false; 
            } catch (Throwable $e) {
                if ($e->errorInfo[0] == 70100||$e->errorInfo[1] == 2006 || $e->errorInfo[1] == 2013) {  
                    sleep(1);//必须的？？
                    $this->reload();
                    continue;
                }
                throw $e;
            }
        }
    }
    public function sql($model):SQL
    {   
        return new SQL($model); 
    }
    public function load($table, ...$pkv):Model
    {
        $arr = array_combine($table::$pks, $pkv);
        if ($row = (new SQL($table))->and($arr)->fetch()) {
            return $row;
        } else {
            throw new \Exception("Error Processing Request", 1);
        } 
    } 

}