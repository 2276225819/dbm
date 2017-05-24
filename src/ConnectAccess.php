<?php namespace dbm;

trait ConnectAccess{

    static $preg_key=[
        '/((?:join|truncate|update|insert(?:\s+into)?|from|create\s+table|alter\s+table|drop\s+table|as)\s+)([a-z_]\w*)/i',
        '/([a-z_]\w*)\s+(read|write)/i',
        '/(\(\s*|,\s*|set\s+|where\s+|and\s+|or\s+)(\b[a-z_]\w*\b)(\s*[=<>!])/i',
        '/(\(\s*|,\s*|set\s+|where\s+|and\s+|or\s+)(\b[a-z_]\w*\b)\s+\bin\b/i',
    ];
    static $preg_val=[
        "$1`$2`",
        "`$1` $2", 
        "$1`$2`$3",
        "$1`$2` in"
    ];
    static function bulidSql($sql){
        return preg_replace(static::$preg_key,static::$preg_val,$sql);    
    }
    public function __construct($dns = null, $name = null, $pass = null,$pf='')
    {  
        $this->dns=$dns;
        $this->name=$name;
        $this->pass=$pass;
        $this->reload();
        class_exists(Sql::class);
        class_exists(Entity::class);
        class_exists(Model::class);
        class_exists(Session::class);
        class_exists(Pql::class);
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
        if(defined("$offset::Entity"))
            return $this->sql($offset);

        if(defined("$offset::Model"))
            return $this->model($offset);
        
        throw new \Exception("Error Processing Request", 1); 
    } 

}