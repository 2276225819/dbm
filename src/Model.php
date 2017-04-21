<?php namespace dbm;

class Model implements \ArrayAccess
{
    public static $table;
    public static $pks=[];
    public static $fks=[];
    public static $cache=[];
    public $bulider=null;

    // public $data=[];
    // public $dirty=[];
    public function pkv(){ 
        foreach (static::$pks as $i => $key) {
            $arr[]=$this->$key;
        }
        return join($arr,'-');
    }
    public function __construct(SQL $bulider=null)
    {  
        $this->bulider = $bulider;
       
        // if(!empty($sql->parent)){  
        //     $a = $sql->parent->model; //user 
        //     unset($sql->parent);
        //     $arr = $sql->fetchAll();
        //     foreach ($arr as $post) {  
        //         static::$cache["$a:$class"][$post->pkv()]=$sql; 
        //     } 
            
        //     // foreach (static::$pks as $i => $key) {
        //     //     if(empty($this->$key))
        //     //         return;
        //     //     $pkv[]=$this->$key;
        //     // }
        //     // $caller = get_class($callee);
        //     // static::$cache["$caller:$class"][join($pkv,'-')]=$callee->bulider; 
        // } 
        // if(!empty($sql->model) && $sql->model==$class){ 
        //     foreach (static::$pks as $i => $key) {
        //         if(empty($this->$key))
        //             return;
        //         $pkv[]=$this->$key;
        //     }
        //     static::$cache[$class][join($pkv,'-')]=$this;
        // }
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
        if (isset(static::$fks[$name])) {
            return $this->hasOne($name, static::$fks[$name]);
        }
        $caller = get_called_class();
        if (class_exists($name) && isset($name::$fks[$caller])) {
            return $this->hasMany($name, $name::$fks[$caller]);
        }
        return $this->dirty[$name]??$this->$name;
    }
    

    public function toArray():array
    {
        $arr = (array)$this;
        unset($arr['bulider']);
        unset($arr['dirty']);
        return $arr;
    }
    public function create(Connect $conn):bool
    {
        if (!empty($this->bulider)) {
            throw new Exception("Error Processing Request", 1);
        }
        $this->bulider = $conn->sql(get_called_class());
        $this->bulider->insert($this->dirty);
        if ($last_id = $this->bulider->lastInsertId()) {
            if (count(static::$pks)==1) {
                $this->{static::$pks[0]} = $last_id; 
            }  
        } 
        foreach (static::$pks as $i => $key) {
            $where[$key]=$this->$key;
        }
        $this->bulider->where($where);
        unset($this->dirty);
        return true;
    }
    public function destroy():bool
    {
        foreach (static::$pks as $i => $key) {
            if (!isset($this->$key)) {
                return false;
            }
            $arr[$key] = $this->$key;
        }
        $query = $this->bulider->new();
        $result = $query->where($arr)->delete();
        return $result;
    }
    public function save():bool
    {
        if (empty($this->dirty)) {
            return false;
        }
        foreach (static::$pks as $i => $key) {
            if (!isset($this->$key)) {
                throw new Exception("Error Processing Request", 1);
            }
            $arr[$key] = $this->$key;
        }
        $query = $this->bulider->new();
        $result = $query->where($arr)->update($this->dirty);
        $this->dirty=[];//clear
        return $result;
    }
    public function hasOne($model)
    {
        if (empty($model::$pks) || empty(static::$fks)) {
            throw new \Exception("Error Processing Request", 1);
        }
        
        foreach ($model::$pks as $i => $key) {
            $pkv[] = $this->{static::$fks[$model][$i]};
        }
        $pkey = join($pkv, '-');
        if (empty(static::$cache[$model][$pkey])) { 
            $query = $this->bulider->new($model);
            foreach ($model::$pks as $i => $key) {
                foreach ($this->bulider->fetchAll() as $value) {
                    $ins[] = $value[  static::$fks[$model][$i] ];
                }
                $query->and([$key=>array_unique($ins)]);
            }
            foreach ($query as $value) {
                $_pkv=array();
                foreach ($model::$pks as $i => $key) {
                    $_pkv[] = $value->$key;
                }
                static::$cache[$model][join($_pkv, '-')]=$value;
            }
        }
        return static::$cache[$model][$pkey];
    }
    public function hasMany($model)
    {
        if (empty($model::$fks) || empty(static::$pks)) {
            throw new \Exception("Error Processing Request", 1);
        }
        
        $query = $this->bulider; 
        $hash = md5( print_r([$query->wStr,$query->wArgs],true) );    
        $caller = get_called_class();   
        if (empty(static::$cache["$caller:$model"][$hash])) {
            $query = $this->bulider->new($model);  
            $qs=[];
            foreach (static::$pks as $i => $key) {
                $arr=array();
                foreach ($this->bulider->fetchAll() as $val) {
                    $arr[] = $val[$key];
                }
                $query->or([ $model::$fks[$caller][$i]=> array_unique($arr) ]);
            }   
            static::$cache["$caller:$model"][$hash]=$query; 
        } 
        $query = static::$cache["$caller:$model"][$hash];  
        foreach ($model::$pks as $i => $key) {
            $pkv[] = $this->$key;
        } 

        $query->sArgs = array_combine( $model::$fks[$caller] , $pkv );   
        return $query;
    }
}
