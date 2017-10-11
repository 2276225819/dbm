<?php namespace dbm\v5;

/**
 * dbm v5.1
 **/
class Collection implements \IteratorAggregate, \ArrayAccess, \JsonSerializable
{

    const Collection = true;
    //代码提示不支持 Private 用trait代替
    use CollectionPrivate;
    public $session;
    public $parent;
    public $refpks;
    public $offset=-1;//当前行
 
    //relation
    static $table='...';
    static $pks=[];
    static $ref=[];
    static $IIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIII='';
    
    /////////// query:model{table} ///////////

    public function load(...$pks)
    {
        $list = $this->find(...$pks)->getAllList();
        if (!empty($list)) {
            $this->offset = key($list);
            $this->data = current($list);
        }
        return $this;
    }
    public function get($offset = null)
    {
        if (is_numeric($offset)) {
            $this->limit(1, $offset);
            $this->offset = -1;
        }
        if ($this->offset===-1) {
            $list = $this->getAllList();
            if (!empty($list)) {
                $this->offset = key($list);
                $this->data = current($list);
                return $this;
            }
        }
    }
    public function sql($table, $pks = [])
    {
        return static::byName($this->session, $table, $pks);
    }
    public function ref($table, $pks = [], $ref = [])
    {
        $model = static::byName($this->session, $table, $pks);
        if (empty($ref) && isset(static::$ref[$table])) {
            $ref = static::$ref[$table];
        }
        if ($this->offset==-1) {
            $keys = join(array_keys($ref), ',');
            $query = $this->sql->field(array_values($ref));
            $model->sql->and([$keys=>$query]);
            $model->parent = $this;
            $model->refpks = $ref;
        } else {
            $list = $this->getAllList(true);
            foreach ($ref as $k => $f) {
                foreach ($list as $row) {
                    $s[$k][]=$row->$f??null;
                }
                if (isset($s[$k])) {
                    $s[$k] = array_unique($s[$k]);
                    sort($s[$k]);
                }
            }
            if (isset($s)) {
                $model->parent = $this;
                $model->refpks = $ref;
                $model->whereAnd($s);
            }
        }
        return $model;
    }

    //////////////// all:mixed ///////////////////

    public function all($field=null)
    {
        $arr=[];
        if(empty($field)){ 
            foreach ($this as $row) {
                $arr[]=clone $row;
            }
            return $arr;
        }
        if(\is_callable($field)){
            foreach ($this as $row) { 
                $arr[] = \call_user_func($field,$row);    
            }
            return $arr;
        }
        if(\is_string($field)){
            foreach ($this as $row) {     
                $arr[] = $row->data->$field;
            }
            return $arr;
        }   

        return $arr;//throw
    }

    public function keypair($key,$field=null)
    {
        $arr=[];
        foreach ($this as $row) {   
            if(empty($field)){
                $arr[$row->data->$key] = clone $row;
                continue;
            } 
            if(\is_callable($field)){
                $arr[$row->data->$key] = \call_user_func($field,$row);
                continue;
            }
            if(\is_string($field)){
                $arr[$row->data->$key] = $row->data->$field;
                continue;
            }
        }
        return $arr;
    }
    public function count($args=1)
    { 
        return (clone $this)->field("count({$args}) as __VALUE__")->val('__VALUE__'); 
    }
    public function avg($args)
    { 
        return (clone $this)->field("avg({$args}) as __VALUE__")->val('__VALUE__'); 
    }
    public function sum($args)
    {
        return (clone $this)->field("sum({$args}) as __VALUE__")->val('__VALUE__'); 
    }
    public function max($args)
    {
        return (clone $this)->field("max({$args}) as __VALUE__")->val('__VALUE__'); 
    }
    public function mix($args)
    {
        return (clone $this)->field("mix({$args}) as __VALUE__")->val('__VALUE__'); 
    }

 
    /////////// filter:self{table sql} ////////////
 
    public function whereAnd($str, ...$args )
    {
        $this->sql->and($str, ...$args);
        return $this;
    }
    
    public function whereOr($str, ...$args )
    {
        $this->sql->or($str, ...$args);
        return $this;
    }

    ////////// curd:model{table sql row} //////////

    public function delete($where = false, ...$args )
    {
        $sql = $this->where($where, ...$args)->sql;
        if (empty($sql->wStr)) {
            throw new \Exception("Require Where Column", 1);
        }
        $count = $this->session->delete($sql);
        return $count;
    }
    public function update($data, ...$arr )
    {

        $param = [];
        $data = $this->sql->kvSQL($param, ',', $data, $arr);
        $str = "UPDATE {$this->sql->table} SET {$data} {$this->sql->wStr}";
        $param = array_merge($param, $this->sql->wArgs);
        if (!($query = $this->session->conn->execute($str, $param))) {
            throw new \Exception("Error Processing Update", 1);
        }
        return $query->rowCount();
    }
    public function replace($data)
    {
        $param = [];
        $str = $this->sql->kvSQL($param, ',', $data);
        $str = "REPLACE {$this->sql->table} SET {$str}";
        $param = array_merge($param, $this->sql->wArgs);
        if (!($query = $this->session->conn->execute($str, $param))) {
            throw new \Exception("Error Processing Update", 1);
        }
        //AUTO INCREMENT
        $data = (object)$data;
        $last_id = $this->session->conn->lastInsertId();
        if (!empty($last_id) && isset($this->sql->pks[0])) {
            $data->{$this->sql->pks[0]}=$last_id;
        }
        // ///////// unpure /////////
        // $this->sql->where($this->sql->pkv($data));
        // $this->cache[$s=(string)$sql]=[ $data ];
        // ///////// unpure /////////
        return $data;
    }

    public function insert(...$list)
    { 
        $param=[];
        $sql1 = "";
        $sql2 = "";
        foreach ($list as &$arr) {
            //关联修改 
            if( isset($this->parent->data)){ 
                foreach($this->refpks as $k=>$v){
                    if(empty($this->parent->data->$v)) {
                        continue;
                    }
                    if(\in_array($k,$this->sql->pks)){
                        continue;
                    }
                    $arr[$k] = $this->parent->data->$v; 
                }
            }
            //$arr = $sql-> array_merge($arr, $sql->sArgs, $sql->rArgs);
            $sql2.=",(".substr(str_repeat(",?", count($arr)), 1).")";
            foreach ($arr as $value) {
                $param[]=$value;
            }
        }
        foreach ($list[0] as $key => $value) {
            $sql1.=",`{$key}`";
        }
        $str="INSERT INTO {$this->sql->table} (".substr($sql1, 1)." )VALUES".substr($sql2, 1);
        if (!($query = $this->session->conn->execute($str, $param))) {
            throw new \Exception("Error Processing Insert Mulit", 1);
        }
        //AUTO INCREMENT
        $data = (object)last($list);
        $last_id = $this->session->conn->lastInsertId();
        if (!empty($last_id) && isset($this->sql->pks[0])) {
            $data->{$this->sql->pks[0]}=$last_id;
        }
        //RELATION CONDITION 
        $model = $this->sql(static::class,$this->sql->pks); 
        if( isset($this->parent) ){  
            foreach($this->refpks as $k=>$v){
                if(isset($data->$k)){
                    $rev[$v] = $data->$k; 
                }
            }
            if(isset($rev) && isset($this->parent->data)){
                $this->parent->save($rev);
            } 
        }
        if(isset($data->{$this->sql->pks[0]})){ 
            foreach ($this->sql->pks as $v) {
                $pkv[$v] = $data->$v;
            }          
            if(isset($pkv)){
                $model->where($pkv);
            }  
        } 
        //CACHED DATA
        $model->session->cache[$s=(string)$model->sql] = [ $model->data = $data ];
        return $model;
    }
    public function save($data = null)
    {
        if ($data===null) {
            //兼容旧版
            if (empty($this->dirty)) {
                throw new \Exception("Error Processing Request", 1);
            }
            if ($this->offset===-1) {
                throw new Exception("Error Processing Request", 1);
            }
            $data = (array)$this->dirty;
            $data += (array)$this->sql->pkv($this->toArray());
            unset($this->dirty); 
        }
        //关联修改
        if(isset($this->data)){ 
            foreach ($data as $key => $value) { 
                if(isset($this->data->$key) && $this->data->$key==$data[$key]){
                    unset($data[$key]);
                }
            }
            if(empty($data)){
                return $this;
            } 
            foreach ($data as $key => $value) {
                $this->data->$key = $value;//bugfix
            }
            foreach($this->sql->pks as $k=>$v){
                $data[$v] = $this->data->$v;
            }
        }

        $param = [];
        $sql1 = '';
        $sql2 = ",(".substr(str_repeat(",?", count($data)), 1).")";
        foreach ($data as $key => $value) {
            $sql1.=",`{$key}`";
            $param[]=$value;
        }
        $data2 = $data;
        $data = (object)$data2;
        foreach ($this->sql->pks as $value) {
            unset($data2[$value]);
        }
        $str="INSERT INTO {$this->sql->table} (".substr($sql1, 1)." )VALUES".substr($sql2, 1);
        if ($sql3 = $this->sql->kvSQL($param, ',', $data2)) {
            $str.=" ON DUPLICATE KEY UPDATE ". $sql3 ;
        }
        if (!($query = $this->session->conn->execute($str, $param))) {
            throw new \Exception("Error Processing Insert" );
        }
        //AUTO INCREMENT
        $last_id = $this->session->conn->lastInsertId();
        if (!empty($last_id) && isset($this->sql->pks[0])) {
        //if (empty($data->{$this->sql->pks[0]}) && !empty($last_id)) {/????
            $data->{$this->sql->pks[0]}=$last_id;
        }
        //RELATION CONDITION
        $model = $this->sql(static::class,$this->sql->pks); 
        if( isset($this->parent) ){  
            foreach($this->refpks as $k=>$v){
                if(isset($data->$k)){
                    $rev[$v] = $data->$k; 
                }
            }
            if(isset($rev) && isset($this->parent->data)){
                $this->parent->save($rev);
            } 
        }
        if(isset($data->{$this->sql->pks[0]})){ 
            foreach ($this->sql->pks as $v) {
                $pkv[$v] = $data->$v;
            }          
            if(isset($pkv)){
                $model->where($pkv);
            }  
        } 
        //CACHED DATA
        $this->session->cache[$s=(string)$model->sql] =  [ $model->data = $data ];
        return $model;
    }
}
