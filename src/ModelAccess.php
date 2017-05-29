<?php namespace dbm;

trait ModelAccess
{

 
    static $table='...';
    static $pks=[];
    static $ref=[];
    
    public static function byName($table, $pks)
    {
        if (class_exists($table) && isset($table::$table)) {
            $sql = new Pql($table::$table, $pks??$table::$pks);
            return new $table($sql);
        } else {
            $sql = new Pql($table, $pks);
            return new Model($sql);
        }
    }
    function __construct($sql = null)
    {
        $this->sql = $sql instanceof Pql?$sql:new Pql(static::$table, static::$pks);
        Session::$gc++;
    }
    function __clone()
    {
        Session::$gc++;
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
        if (isset($this->list[0])) {
            return $this->list[0];
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
        $vals = Session::$instance->select($this->sql->field($attr));
        return $vals[0]['__VALUE__'];
    }
    /**
     * hash 
     */
    function __toString()
    {
        return (string)$this->sql;
    }

    function getIterator()
    {
        if (!isset($this->list)) {
            $this->list = Session::$instance->select($this->sql);
        }
        $model = clone $this;//new static($this->sql);
        $pks = $model->sql->pks;
        foreach ($this->list as $row) {
            foreach ($pks as $pk) {
                if (isset($row[$pk])) {
                    $model->sql->rArgs[$pk]=$row[$pk];
                }
            }
            $model->list = [$row];
            yield $model;
        }
    }


    function jsonSerialize()
    {
        return $this->list[0];
    }
    function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }
    function offsetUnset($offset)
    {
        throw new Exception("Error Processing Request", 1);
        //unset($this->data[$offset]);
    }
    function offsetSet($offset, $value)
    { 
        if(is_null($offset) && is_array($value)){
            return $this->insert($value); 
        }else{
            return $this->val($offset,$value); 
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
        if (!isset($this->list)) {
            $this->list = Session::$instance->select($this->sql);
        }
        return $this->list[0];
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
    public function create(...$args)
    {
        return $this->save(...$args);
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
}
