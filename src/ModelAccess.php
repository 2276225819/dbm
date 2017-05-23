<?php namespace dbm;

trait ModelAccess
{ 
    /**
     * @var Sql
     */
    public $sql; 

    function __construct($sql=null)
    {
        $this->sql = $sql??new Pql;
        Session::$gc++; 
    }
    function __clone(){
        Session::$gc++; 
    }
    function __destruct(){
        Session::$gc--; 
        if(Session::$gc==0){
            Session::$instance->clean(); 
        }
    }
 
    function __debugInfo()
    {
        // if (!isset($this->list)) {
        //     $this->list = Session::$instance->select($this->sql);
        // }
        if (isset($this->list[0])) {
            return $this->list[0];
        } else {
            return [static::class=>(string)$this->sql];
        }
        //throw new \Exception("Error Processing Request", 1);
    }
    function __toString()
    {
        return (string)$this->sql;//->getHash();
    }
    function __invoke(...$pkv)
    {
        return $this->find(...$pkv);
    }
    function __call($name, $args)
    {
        if (!count($args)) {
            $args[0]='1';
        }
        $attr = "$name({$args[0]}) as __VALUE__";
        $vals = Session::$instance->select($this->sql->field($attr));
        return $vals[0]['__VALUE__'];
    }
    function getIterator()
    {
        if (!isset($this->list)) {
            $this->list = Session::$instance->select($this->sql);
        }
        foreach ($this->list as $row) {
            $model = new static($this->sql);
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
        //return isset($this->data[$offset]);
    }
    function offsetUnset($offset)
    {
        //unset($this->data[$offset]);
    }
    function offsetSet($offset, $value)
    {
        if (empty($this->data[$offset]) || $this->data[$offset]!=$value) {
            $this->dirty[$offset]=$value;
        }
        $this->data[$offset]=$value;
    }
    function offsetGet($offset)
    {
        if (is_numeric($offset)) {
            return $this->get($offset);
        } elseif (defined("$offset::pks") && defined("static::ref")) {
            return $this->ref($offset, $offset::pks, static::ref[$offset]); 
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
        return $this->list;
    }



    function find(...$pkv)
    {
        if (is_array($pkv[0] && empty($pkv[0][0]))) {
            $arr = $pkv[0];
        } else {
            $arr = array_combine($this->sql->pks, $pkv);
        }
        $this->sql->rArgs=$arr;
        return $this->and($arr);
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
