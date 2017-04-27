<?php namespace dbm;

class SQL implements \IteratorAggregate, \ArrayAccess
{
    public $conn;#Connect
    public $tname;#String
    public $model;#ClassString?
    public $parent;#SQL?
    public $caches=[];
    public function __construct(Connect $conn, string $model, &$caches = [])
    {
        $this->conn=$conn;
        $this->caches=&$caches;
        if (class_exists($model) && isset($model::$table)) {
            $this->tname = $model::$table??$model;
            $this->model = $model;
        } else {
            $this->tname=$model;
        }
    }
    public function __toString()
    {
        return "SELECT {$this->fStr} FROM {$this->tname} {$this->wStr} {$this->oStr} {$this->lStr}";
    }
    public function getIterator()
    {
        $all = $this->fetchAll(); 
        foreach ($all as $value) {
            if ($this->rArgs) {
                foreach ($this->rArgs as $k => $v) {
                    if ($value[$k]!=$v) {
                        continue 2;
                    }
                }
            }
            $arr[]=$value;
        }
        return new \ArrayIterator($arr??[]);
    }
    public function fetchAll($style=\PDO::FETCH_CLASS)
    {
        $args = array_merge($this->wArgs, $this->oArgs);
        $sql = "$style|$this;".implode($args, ','); 
        if (empty($this->caches[$sql])) {
            if (!($query = $this->execute((string)$this, $args))) {
                throw new \Exception("Error Processing Query" );
            }
            switch($style){
                case \PDO::FETCH_CLASS:
                    $query->setFetchMode(\PDO::FETCH_CLASS,
                        $this->model??Model::class, [$this->conn, $this ]
                    );
                    break; 
                default:
                    $query->setFetchMode($style);
                    break;
            } 
            $this->caches[$sql] = $query->fetchAll();
        }
        return $this->caches[$sql];
    }
    public function fetch($style=\PDO::FETCH_CLASS){ 
        $all = $this->fetchAll($style); 
        return current($all); 
    }
    public function offsetExists($offset)
    {
    }
    public function offsetUnset($offset)
    {
    }
    public function offsetSet($name, $value)
    {
    }
    public function offsetGet($name)
    {
        foreach ($this as $row) {
            return $row[$name];
        }
    }



    public function from(string $model = null):SQL
    {
        return new self($this->conn,$model??$this->model??$this->tname,$this->caches);
    }
     
    public function value($field){
        return $this->field($field)->fetch()[$field];
    }
    public function list($key)
    { 
        foreach($this as $row){
            $arr[] = $key?$row[$key]:$row;
        }
        return $arr??[]; 
    }
    public function keypair($key,$val=null)
    { 
        foreach($this as $row){
            $arr[$row[$key]] = $val?$row[$val]:$row;
        }
        return $arr??[]; 
    }
    ///////////////////////////////////////
    public function execute($sql, $args = []) //:mixed?
    {
        return $this->conn->execute($sql, $args);
    }

    public function insert($data, $auto_increment_key = null)
    {
        $data = array_merge($data,$this->rArgs, $this->sArgs);
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
        $row = new $this->model($this->conn,$this);
        foreach ($data as $key => $value) {
            $row->$key=$value;
        }
        return $row;
    }
    
    public $wStr='',$lStr='',$oStr='',$fStr='*';
    public $rArgs=[],$wArgs=[], $fArgs=[], $sArgs=[],$oArgs=[];
    public function lastInsertId() :int
    {
        return $this->conn->lastInsertId();
    }
    public function insertMulit($list) :int
    {
        $param=[];
        $sql1 = "";
        $sql2 = "";
        foreach ($list as &$arr) {
            $arr = array_merge($arr, $this->sArgs,$this->rArgs);
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
    public function limit($limit, $offset = 0):SQL
    {
        
        $this->lStr=" LIMIT ".intval($limit);
        if (!empty($offset)) {
            $this->lStr.=' OFFSET '.intval($offset).' ';
        }
        return $this;
    }
    public function order(string $order, ...$arr) :SQL
    {
        $this->oStr=" ORDER BY ".$order;
        $this->oArgs=$arr;
        return $this;
    }
    public function field($fields) :SQL
    {
        $this->fStr=$this->kvSQL($this->fArgs, ',', $fields );
        return $this;
    }

    public function where($w, ...$arr) :SQL
    {
        $this->wStr=' WHERE '.$this->kvSQL($this->wArgs, ' AND ', $w, $arr);
        return $this;
    }
    public function and($w, ...$arr) :SQL
    {
        $this->wStr.=empty($this->wStr)?" WHERE ":" AND ";
        $this->wStr.=$this->kvSQL($this->wArgs, ' AND ', $w, $arr);
        return $this;
    }
    public function or($w, ...$arr) :SQL
    {
        $this->wStr.=empty($this->wStr)?" WHERE ":" OR ";
        $this->wStr.=$this->kvSQL($this->wArgs, ' OR ', $w, $arr);
        return $this;
    }

    private function kvSQL(&$param, $jtag = ' AND ', $arr, $attr = null, $sql = ''):string
    {
        if (is_array($arr)) {
            foreach ($arr as $key => $v) {
                if (is_array($v)) {
                    if (count($v)>1) {
                        $str= substr(str_repeat(",?", count($v)), 1);
                        $sql.="{$jtag} {$key} in ($str) " ;
                        $param=array_merge($param, $v);
                        continue;
                    } else {
                        $v=$v[0];
                    }
                }
                $sql.= "{$jtag}{$key}=?";
                $param[]=$v;
            }
            $sql=substr($sql, strlen($jtag));
        } else {
            $sql=$arr;
            if (is_array($attr)) {
                $param=array_merge($param, $attr);
            }
        }
        return $sql;
    }
}
