<?php namespace dbm;
  
trait CollectionPrivate
{

    function __construct($table, $pks,  $session, $parent = null)
    {
        $this->sql = new Pql($table, $pks);//, $ref);
        $this->session = $session;
        $this->parent = $parent;
    }
    function __clone()
    {
        $this->sql = clone $this->sql;//BUG
    }
    function __call($name, $args)
    {
        if (method_exists($this->sql, $name)) {
             $this->sql->$name(...$args);
             return $this;
        }
        throw new \Exception("Error: [ $name ]", 1); 
    }   
    function __invoke(...$pkv)
    {
        return $this->load(...$pkv);
    }
    function __debugInfo()
    { 
        if(empty($this->data)){ 
            $arr[":"]=(string)$this->sql;
            if (isset($this->sql->rArgs)) {
                $arr['?']=json_encode($this->sql->rArgs);
            }
            return $arr;
        }else{
            return $this->toArray();
        } 
    } 
    function __toString()
    {
        return (string)$this->sql;
    }
    function jsonSerialize()
    {
        return $this->toArray();
    }
    function offsetExists($offset)
    {
        return $this[$offset];
    }
    function offsetUnset($offset)
    {
        throw new Exception("Error Processing Request", 1);
    }
    function offsetSet($offset, $value)
    {
        if (is_null($offset) && is_array($value)) {
            return $this->insert($value);
        } else {
            return $this->val($offset, $value);
        }
    }
    function offsetGet($offset)
    {
        // 查分多条语句可以分别缓存，所以使用get(0)，不用limit(1,0)
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

    
    ////////// curd:model{table sql row} //////////
    
    public function getAllList($all = false)
    {
        
        $hash = (string)$this->sql;
        if (!isset($this->session->cache[$hash])) {
            $ssql = $this->sql->bulidSelect();
            $args = $this->sql->bulidArgs();
            $fetch = $this->session->conn->execute($ssql, $args);
            $fetch->setFetchMode(\PDO::FETCH_OBJ);
            $this->session->cache[$hash]=$fetch->fetchAll();
            //$this->offset = 0;
        }
        if ($all or empty($this->parent) or $this->parent->offset===-1) {
            // if($this->offset===-1){
            //     $this->offset = 0;
            // }
            return $this->session->cache[$hash];
        } else {
            $list = $this->parent->getAllList();
            $filter = $list[$this->parent->offset];
            foreach ($this->session->cache[$hash] as $index => $row) {
                foreach ($this->refpks as $k => $v) {
                    if ($row->$k!=$filter->$v) {
                        continue 2;
                    }
                }
                $arr[$index]=$row;
            }
            // if($this->offset===-1){
            //     $this->offset=isset($arr)?key($arr):0;
            // }
            return $arr??[];
        }
    }
    public function val($key, $val = null)
    {
        if ($key===null) {
            return;
        }
        if ($val===null) { 
            if(isset($this->data->$key)){
                return $this->data->$key;
            }
            if(isset($this->dirty->$key)){
                return $this->dirty->$key;
            }
            $list = $this->toArray();
            return $list[$key]??null;
        }
        //兼容v4 
        if(empty($this->data->$key) || $this->data->$key != $val){
            $this->dirty[$key] = $val;
        } 
        // if (isset($this->data)) {
        //     $this->data->$key = $val; 
        // }
    }
    public function toArray()
    {
        if (isset($this->data)) {
            return (array)$this->data;//查询优化
        }
        $data = $this->getAllList();
        if ($this->offset==-1) {
            $this->offset = key($data);
        }
        if (isset($data[$this->offset])) {
            $this->data = $data[$this->offset];
            return (array)$data[$this->offset];
        }
    }
    public function getIterator()
    {
        $model = clone $this;
        $data = $this->getAllList();
        foreach ($data as $key => $row) {
            $model->offset = $key;
            $model->data = $row;//查询优化
            yield $model;
        }
    }
    static function byName($session, $table, $pks)
    {
        if (class_exists($table) && isset($table::$table)) {
            $pks = (array)$pks+(array)$table::$pks;
            return new $table($table::$table, $pks,$session);
        } else {
            $pks = (array)$pks;
            return new self($table,$pks,$session);
        }
    }

    ///////////////  兼容旧版(弃用) /////////////////////

    function insertMulit($args)
    { 
        $this->insert(...$args);
        return count($args);
    }
    
    function set($data)
    { 
        //关联修改 
        if( isset($this->parent->data)){ 
            foreach($this->refpks as $k=>$v){
                if(isset($this->parent->data->$v)){
                    $data[$k] = $this->parent->data->$v; 
                }
            }
        } 
        //$data += $this->sql->rArgs;
        foreach ($this->sql->pks as $key) {
            if (isset($data[$key])) {
                $where[$key]=$data[$key];
            }
        }
        if (isset($where)) {
            if ($row = $this->where($where)->get()) { 
                return $row->save($data);  
            }
        }
        return $this->insert($data); 
    }

    function map($fn){
        return $this->all($fn);
    }

    public function destroy(...$args)
    {
        return $this->delete(...$args);
    }
}
