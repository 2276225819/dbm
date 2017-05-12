<?php namespace dbm;

trait ConnectAccess{

    public function __construct($dns = null, $name = null, $pass = null)
    { 
        $this->dns=$dns;
        $this->name=$name;
        $this->pass=$pass;
        $this->__reload(); 
        //autoload
        class_exists(Sql::class);
        class_exists(Model::class);
    }
    public function __reload()
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