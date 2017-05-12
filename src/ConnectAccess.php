<?php namespace dbm;

trait ConnectAccess{

    public $preg_key;
    public $preg_val;
    public function __construct($dns = null, $name = null, $pass = null,$pf='')
    { 
        $arr=array(
            '/(\s)([a-z_]\w*)\.([a-z_]\w*)/i'
                =>"$1`$2`.`$3`",
            '/((?:join|truncate|from|create table|alter table|as)\s+)([a-z_]\w*)/i'
                =>"$1`$2`",
            '/([a-z_]\w*)\s+(read|write|set)/i'
                =>"`$1` $2",
            '/(\s)([a-z_]\w*)\s*=/i'
                =>"$1`$2`=",
            '/([a-z_]\w*)\s+\bin\b/i'
                =>"`$1` in"
        );
        $this->preg_key=array_keys($arr);
        $this->preg_val=array_values($arr); 
        $this->dns=$dns;
        $this->name=$name;
        $this->pass=$pass;
        $this->reload();
        class_exists(Sql::class);
        class_exists(Model::class);
    } 
    public function reload()
    {
        $this->db = new \PDO($this->dns, $this->name, $this->pass, $this->attr);
    }
    
    public function offsetUnset($offset)
    {
    }
    public function offsetSet($offset, $value)
    {
    }
    public function offsetExists($offset)
    {  
    }  
    public function offsetGet($offset)
    {
        return $this->sql($offset);
    } 

}