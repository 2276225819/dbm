<?php namespace dbm;

class SQL implements \Iterator
{ 
    public $query;
    public $tname;
    public $model;
    public function __construct(string $model)
    {
        if (class_exists($model) && isset($model::$table) ) {
            $this->tname = $model::$table??$model;
            $this->model = $model;
        } else {
            $this->tname=$model;
        }
    }
    public function __toString(){
        return "SELECT {$this->fStr} FROM {$this->tname} {$this->wStr} {$this->oStr} {$this->lStr}"; 
    }
    public function new(string $model = null):SQL
    { 
        return new self($model??$this->model);
    }
    
    function access()
    { 
        $args =  array_merge($this->fArgs, $this->wArgs);
        if (!($this->query = $this->execute((string)$this, $args))) { 
            throw new \Exception("Error Processing Query" );
        }
        if (isset($this->model)) {
            $this->query->setFetchMode(\PDO::FETCH_CLASS, 
                $this->model, [ $this ]
            );
        } else {
            $this->query->setFetchMode(\PDO::FETCH_ASSOC);
        }
    }
    public $each=[];
    public $each_pos=0;
    function rewind()
    { 
        $this->each = $this->fetchAll();
        $this->each_pos=0;
        if(!empty($this->sArgs)){
            $this->each = array_filter($this->each,function($a){
                foreach ($this->sArgs as $k => $v) {
                    if($a->$k!=$v)  return false;
                }
                return true; 
            });  
        } 
    }
    function current()
    {
        return $this->each[$this->each_pos]; 
    }
    function key()
    {
        return $this->each_pos; 
    }
    function next()
    {
       return ++$this->each_pos; 
    }
    function valid()
    {
        return isset($this->each[$this->each_pos]); 
    }
    
    public $_array=[];
    public $_pos=0;
    public $_end=false;
    public function fetchAll()
    {
        if (empty($this->query)) {
            $this->access();
        }
        if (empty($this->_end)) { 
            if ($all = $this->query->fetchAll()) {
                $this->_array = array_merge($this->_array, $all);
            }
            $this->_end=true;
        }
        return $this->_array;
    }
    public function fetch()
    {
        if (empty($this->query)) {
            $this->access();
        }
        if (empty($this->_end)) {
            if ($this->_pos>2) {
                if ($all = $this->query->fetchAll()) {
                    $this->_array = array_merge($this->_array, $all);
                }
                $this->_end=true;
            } else {
                if ($row=$this->query->fetch()) {
                    $this->_array[]=$row;
                } else {
                    $this->_end=true;
                }
            }
        } 
        return $this->_array[$this->_pos++];
    }
    
    public function value($column=0) {
        $args = array_merge($this->fArgs, $this->wArgs);
        if ($this->query = $this->execute((string)$this, $args)) {  
            if ($row=$this->query->fetch(\PDO::FETCH_BOTH)) {
                return $row[$column];
            }
        }
    }
    public function list(){
        $args = array_merge($this->fArgs, $this->wArgs);
        if ($this->query = $this->execute((string)$this, $args)) {  
            return $this->query->fetchAll(\PDO::FETCH_COLUMN,0); 
        } 
    }
    public function keypair(){
        $args = array_merge($this->fArgs, $this->wArgs);
        if ($this->query = $this->execute((string)$this, $args)) {  
            return $this->query->fetchAll(\PDO::FETCH_KEY_PAIR); 
        } 
    }
    ///////////////////////////////////////
    public function execute($sql, $args = []) //:mixed?
    {
        return Connect::$currect->execute($sql, $args);
    }

    public function insert($data, $auto_increment_key = null)
    {
        $data = array_merge($data, $this->sArgs);
        $sql="INSERT INTO {$this->tname} SET ".$this->kvSQL($param, ',', $data);
        if (!($query = $this->execute($sql, $param))) { 
            throw new \Exception("Error Processing Insert" );
        }
        //AUTO INCREMENT
        $last_id = $this->lastInsertId();
        if (!empty($auto_increment_key)) {
            $data[$auto_increment_key]=$last_id;
        }
        if (!class_exists($this->model)) {
            return $data;
        }
        if (!empty($this->model::$pks) && count($this->model::$pks)==1) {
            $data[$this->model::$pks[0]]=$last_id;
        }
        $row = new $this->model($this);
        foreach ($data as $key => $value) {
            $row->$key=$value;
        }
        return $row;
    }
    
    public $wStr='',$lStr='',$oStr='',$fStr='*';
    public $wArgs=[], $fArgs=[], $sArgs=[];
    public function lastInsertId() :int
    {
        return Connect::$currect->lastInsertId();
    }
    public function insertMulit($list) :int
    {
        $param=[];
        $sql1 = "";
        $sql2 = "";
        foreach ($list as &$arr) {
            $arr = array_merge($arr, $this->sArgs);
            $sql2.=",(".substr(str_repeat(",?", count($arr)), 1).")";
            array_push($param, ...array_values($arr));
        }
        foreach ($list[0] as $key => $value) {
            $sql1.=",`{$key}`";
        }
        $sql="INSERT INTO {$this->tname} (".substr($sql1, 1)." )VALUES".substr($sql2, 1);
        if (!($query = $this->execute($sql, $param))) { 
            throw new \Exception("Error Processing Insert Mulit", 1);
        }
        return $query->rowCount();
    }
    public function update($data, ...$arr) :int
    {
        if (empty($this->wStr)) {
            throw new \Exception("Require Where Column", 1);
        }
        $param=[];
        $data=$this->kvSQL($param, ',', $data, $arr);
        $sql="UPDATE {$this->tname} SET {$data} {$this->wStr}";
        $param = array_merge($param, $this->wArgs);
        if (!($query = $this->execute($sql, $param))) { 
            throw new \Exception("Error Processing Update", 1);
        }
        return $query->rowCount();
    }
    public function delete() :int
    {
        if (empty($this->wStr)) {
            return false;
        }
        $sql="DELETE FROM {$this->tname} {$this->wStr}";
        if (!($query = $this->execute($sql, $this->wArgs))) { 
            throw new \Exception("Error Processing Delete", 1);
        }
        return $query->rowCount();
    }
    public function limit($offset, $limit = null):SQL
    { 
        $this->lStr=" LIMIT ".intval($offset);
        if(!empty($limit))$this->lStr.=' , '.intval($limit).' '; 
        return $this;
    }
    public function order(string $order) :SQL
    {
        $this->oStr=" ORDER BY ".$order;
        return $this;
    }
    public function field($fields) :SQL
    {
        $this->fStr=$this->kvSQL($this->fArgs, ',', $fields );
        return $this;
    } 

    public function where($w, ...$arr) :SQL
    {
        $this->wStr=' WHERE '.$this->kvSQL($this->wArgs, 'AND', $w, $arr);
        return $this;
    }
    public function and($w, ...$arr) :SQL
    {
        $this->wStr.=empty($this->wStr)?" WHERE ":" AND ";
        $this->wStr.=$this->kvSQL($this->wArgs, 'AND', $w, $arr);
        return $this;
    }
    public function or($w, ...$arr) :SQL
    {
        $this->wStr.=empty($this->wStr)?" WHERE ":" OR ";
        $this->wStr.=$this->kvSQL($this->wArgs, 'OR', $w, $arr);
        return $this;
    }

    private function kvSQL(&$param, $join = 'AND', $arr, $attr = null, $sql = ''):string
    {
        if (is_array($arr)) {
            foreach ($arr as $key => $v) {
                if (is_array($v)) {
                    $str= substr(str_repeat(",?", count($v)), 1);
                    $sql.="{$join} {$key} in ($str)";
                    $param=array_merge($param, $v);
                } else {
                    $sql.= "{$join} {$key}=?";
                    $param[]=$v;
                }
            }
            $sql=substr($sql, strlen($join));
        } else {
            $sql=$arr;
            if (is_array($attr)) {
                $param=array_merge($param, $attr);
            }
        }
        return $sql;
    }
}
