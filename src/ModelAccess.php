<?php namespace dbm;

trait ModelAccess
{
    //public $data=[];
    public $dirty=[];
 
    static $table='...';
    static $pks=[];
    static $ref=[];
  
    function __construct($sql = null, $data = null)
    {
        if (!$sql instanceof Pql) {
             $sql = new Pql(static::$table, static::$pks);
        }
        if(!empty($data)){ 
            $data = (object)$data;
            foreach ($sql->pks as $key) {
                if (isset($data->$key)) {
                    $sql->rArgs[$key]=$data->$key;
                }
            }
            $sql->where($sql->rArgs);
            Session::$instance->cache[$s=(string)$sql]=[$data];
            $this->data =  $data;
        }
        $this->sql = $sql;
        //$this->data = $data; 
        Session::$gc++;
    }
    function getIterator()
    {
        $list  = Session::$instance->select($this->sql); 
        $model = new static(clone $this->sql); 
        $pks = $model->sql->pks;
        foreach ($list as $data) {
            foreach ($pks as $pk) {
                if (isset($data->$pk)) {
                    $model->sql->rArgs[$pk]=$data->$pk;
                }
            }
            $model->data = (object)$data;
            yield $model;
        }
    }

    function __clone()
    {
        Session::$gc++;
        $this->sql = clone $this->sql;//BUG
    }
    function __destruct()
    {
        Session::$gc--;
        if (Session::$gc==0) {
            Session::$instance->clean();
        }
    }
 
    function __debugInfo()
    {
        //return (array)$this;
        // if (!isset($this->list)) {
        //     $this->list = Session::$instance->select($this->sql);
        // }
        if (!empty($this->data)) {
            return (array)$this->data;
        } else {
            $arr[":"]=(string)$this->sql;
            if (isset($this->sql->rArgs)) {
                $arr['?']=json_encode($this->sql->rArgs);
            }
            return $arr;
        }
    }
    /**
     * 查分多条语句可以分别缓存，所以使用load(id)不用find(id)
     */
    function __invoke(...$pkv)
    {
        return $this->load(...$pkv);
    }
    /**
     * 聚合函数
     */
    function __call($name, $args)
    {
        if (!count($args)) {
            $args[0]='1';
        }
        $attr = "$name({$args[0]}) as __VALUE__";
        $this->sql->rArgs=[];//BUG
        $vals = Session::$instance->select($this->sql->field($attr));
        return $vals[0]->__VALUE__;
    }
    /**
     * hash
     */
    function __toString()
    {
        return (string)$this->sql;
    }


    function jsonSerialize()
    {
        return $this->data;
    }
    function offsetExists($offset)
    {
        return $this[$offset];
    }
    function offsetUnset($offset)
    {
        throw new Exception("Error Processing Request", 1);
        //unset($this->data[$offset]);
    }
    function offsetSet($offset, $value)
    {
        if (is_null($offset) && is_array($value)) {
            return $this->insert($value);
        } else {
            return $this->val($offset, $value);
        }
    }
    
    /**
     * 查分多条语句可以分别缓存，所以使用get(0)，不用limit(1,0)
     */
    function offsetGet($offset)
    {
        if (is_numeric($offset)) {
            return $this->get($offset);
        } elseif (class_exists($offset) && isset($offset::$pks) && isset(static::$ref)) {
            return $this->ref($offset, $offset::$pks, static::$ref[$offset]);
        } elseif (isset($this->sql->rArgs[$offset])) {
            return $this->sql->rArgs[$offset];
        } else {
            return $this->val($offset);
        }
    }
    function toArray()
    {
        return (array)($this->data??[]);
    }



    function each($cb)
    {
        foreach ($this as $value) {
            if ($cb($value)===false) {
                break;
            }
        }
    }
    function map($cb)
    {
        foreach ($this as $value) {
            $arr[]=$cb($value);
        }
        return $arr??[];
    }



    public function destroy(...$args)
    {
        return $this->delete(...$args);
    }
    public function and(...$args)
    {
        return $this->whereAnd(...$args);
    }
    public function or(...$args)
    {
        return $this->whereOr(...$args);
    }
    public function create()
    {
        $row = $this->insert($this->dirty);
        $this->data = $row->data;
        $this->sql = $row->sql; 
    } 
    public function save()
    { 
        // $dirty += $this->dirty; 
        // if (!count($dirty)) {
        //     //->set(no changed)
        //     return $this; 
        // } 
        if(!count($this->dirty)){ 
            //->set(no changed)
            return $this;  
        }
        $model = empty($this->data)?$this:clone $this;
        if(!empty($model->sql->rArgs)){
            $model->where($model->sql->rArgs);
        } 
        if(empty($model->sql->wStr) || empty($model->data)){  
            $model->create( ); 
        }else{
            $model->update($this->dirty); 
        }
  
        $this->dirty=[];
        return $this; 
    }

    
      
    /**
     * Model{count=1}
     * @param array $data
     * @return \dbm\Model
     */
    public function set($data)
    { 
        $data += $this->sql->rArgs;
        foreach ($this->sql->pks as $key) {
            if (isset($data[$key])) {
                $where[$key]=$data[$key];
            }
        }
        if (isset($where)) {
            if ($row = (clone $this)->where($where)->get()) {
                foreach ($data as $key => $value) {
                    $row[$key]=$value;
                }
                $row->save();
                return $row;
            }
        }
        return $this->insert($data);
    }
    
    /**
     * ... FROM [TABLE] JOIN {$str} ...
     * @param string  $str
     * @return Sql
     */
    public function join($str)
    {
        $this->sql->join($str);
        return $this;
    }     
    /**
     * ... GROUP BY {$str} ...
     * @return \dbm\Model
     */
    public function group($str){
        $this->sql->group($str);
        return $this; 
    }

    
    public function many($model, $model_pks, $model_fks)
    {
        return $this->ref($model, (array)$model_pks,
            array_combine((array)$model_fks, (array)$model_pks)
        );
    }
    public function one($model, $model_pks, $local_fks)
    {
        return $this->ref($model, (array)$model_pks,
            array_combine((array)$model_pks, (array)$local_fks)
        );
    }
    public function sql($model, $pks = null){
        return self::byName($model,$pks);
    }

      
    public static function byName($table, $pks = null)
    {
        if (class_exists($table) && isset($table::$table)) {
            $sql = new Pql($table::$table, $pks?:$table::$pks);
            return new $table($sql);
        } else {
            $sql = new Pql($table, $pks);
            return new Model($sql);
        }
    }
}
