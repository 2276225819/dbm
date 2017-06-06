<?php namespace dbm;

trait ConnectAccess
{
    static $conn=[];
    public $attr=[
        \PDO::ATTR_ERRMODE=>\PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_PERSISTENT=>true,
    ]; 
    public function __construct($dns = null, $name = null, $pass = null, $attr = [])
    {
        $this->dns=$dns;
        $this->name=$name;
        $this->pass=$pass;
        $this->attr=array_merge($this->attr,$attr);
        if(empty(static::$conn[$this->dns])){
            $this->reload(); 
        }
        class_exists(Sql::class);
        class_exists(Entity::class);
        class_exists(Model::class);
        class_exists(Session::class);
        class_exists(Pql::class);


        Session::$instance = new Session($this); 
    } 
    public function reload()
    {  
        //相同dns可能会共用`链接`和`事务`（ATTR_PERSISTENT）
        //声明多个相同dns链接，启用其中一个链接事务然后删除另一个链接，会停止所有相同dns链接的事务。
        $db = new \PDO($this->dns, $this->name, $this->pass, $this->attr);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        static::$conn[$this->dns]=$db; 
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
        if (defined("$offset::Entity")) {
            return $this->entity($offset);
        }

        if (defined("$offset::Model")) {
            return $this->sql($offset);
        }
        
        throw new \Exception("Error Processing Request", 1);
    }





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
    static function bulidSql($sql)
    {
        return preg_replace(static::$preg_key, static::$preg_val, $sql);
    }
}
