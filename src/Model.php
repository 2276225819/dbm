<?php namespace dbm;

class Model implements \ArrayAccess
{
    public static $table;//useby SQL
    public static $pks=[];//useby SQL
    public static $fks=[];//useby SQL 
    // private $bulider=null;
    // private  $dirty=[];
    public function __construct(Connect $conn,SQL $bulider = null)
    {
        $this->bulider = $bulider?$bulider:$conn->sql(get_called_class());
    }
 
  
    /////////////////////////////////////////////////////
    public function offsetExists($offset)
    {
    }
    public function offsetUnset($offset)
    {
    }
    public function offsetSet($name, $value)
    {
        $this->dirty[$name]=$value;
        $this->$name=$value;
    }
    public function offsetGet($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        }

        if (class_exists($name)) {
            if (isset(static::$fks[$name])) {
                return $this->hasOne($name, $name::$pks, static::$fks[$name]);
            }
            $caller = get_called_class();
            if (isset($name::$fks[$caller])) {
                return $this->hasMany($name, $name::$fks[$caller], static::$pks);
            }
        }
    }
    public function hasOne($model, $pks, $fks):SQL
    {
        $pks=(array)$pks;
        $fks=(array)$fks;
        $sql = $this->bulider->from($model);
        foreach ($pks as $i => $k) {
            $sql->rArgs[$k]=$this[$fks[$i]];
            //$tmp->rArgs[$k]=$this[$fks[$i]];
        }
        foreach ($this->bulider->fetchAll() as $obj) {
            $test[]=$obj;
            foreach ($fks as $i => $k) {
                $arr[$pks[$i]][] = $obj[ $k ];
            }
        }
        foreach ($arr as &$unique) {
            $unique=array_unique($unique);
        } 
        return $sql->and($arr);
    }
    public function hasMany($model, $pks, $fks):SQL
    {
        $pks=(array)$pks;
        $fks=(array)$fks;
        $sql =  $this->bulider->from($model);
        foreach ($pks as $i => $k) {
            $sql->rArgs[$k]=$this[$fks[$i]];
        }
        foreach ($this->bulider as $obj) {
            foreach ($fks as $i => $k) {
                $arr[$pks[$i]][] = $obj[ $k ];
            }
        }
        return $sql->or($arr);
    }
 
    
    public function pkv($pks = null)
    {
        foreach ($pks??static::$pks as $i => $key) {
            if (!isset($this->$key)) {
                return false;
            }
            $arr[$key] = $this->$key;
        }
        return $arr;
    }
    public function toArray():array
    {
        $arr = (array)$this;
        unset($arr['bulider']);
        unset($arr['dirty']);
        return $arr;
    }
    public function create($pks=null):bool
    { 
        if(empty($pks))$pks = static::$pks;
         
        $this->bulider->insert($this->dirty);
        if ($last_id = $this->bulider->lastInsertId()) {
            if (count($pks)==1) {
                $this->{$pks[0]} = $last_id;
            }
        }
        //???????????????????
        if ($arr = $this->pkv($pks)) {
            $this->bulider->where($arr);
        }
        unset($this->dirty);
        return true;
    }
    public function destroy($pks=null):bool
    {
        if(empty($pks))$pks = static::$pks;

        if (!($arr = $this->pkv($pks))) {
            throw new Exception("Error Processing Request", 1);
        }
            
        $query = $this->bulider->from();
        $result = $query->where($arr)->delete();
        return $result;
    }
    public function save($pks = null):bool
    {
        if (empty($this->dirty)) {
            return false;
        }
        if (!($arr = $this->pkv($pks))) {
            throw new Exception("Error Processing Request", 1);
        }
            
        $query = $this->bulider->from();
        $result = $query->where($arr)->update($this->dirty);
        $this->dirty=[];//clear
        return $result;
    }
}
