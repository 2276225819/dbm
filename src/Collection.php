<?php namespace dbm;

/**
 * dbm v5
 **/
class Collection extends \ArrayObject
{
    use CollectionCompatible;
    public $session;#{cache:{sqlhash:[]}}
    public $tablename ,$tablepks ;//,$ref=[]; ,//$dirty=[]; //*ArrayObject*storage
    static function new($table, $pks, $session)
    {
        if (class_exists($table) && isset($table::$table)) {
            $model = new $table;
            $model->session = $session;
            $model->tablepks = (array)$pks+(array)$table::$pks;
            $model->tablename = $table::$table;
        } else {
            $model = new self;
            $model->session = $session;
            $model->tablepks = (array)$pks;
            $model->tablename = $table;
        }
        return $model->uncache();
    }
 
    /////////// query:model{table} ///////////
    
    public function sql($table, $pks = null)
    {
        return static::new($table, $pks, $this->session);
    }
    public function ref($table, $pks = [], $ref = [])
    {
        $model = static::new($table, $pks, $this->session );
        if (empty($ref) && isset(static::$ref[$table])) {
            $ref = static::$ref[$table];
        }
        if ($this->hasRow()) {
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
        } else {
            $keys = join(array_keys($ref), ',');
            $model->and([ $keys => (clone $this)->field(array_values($ref)) ]);
            $model->parent = $this;
            $model->refpks = $ref;
        }
        return $model;
    }
    public function get($offset = null)
    {
        $model = clone $this;
        if (is_numeric($offset)) {
            return $model->limit(1, $offset)->get();
        }
        if (!$this->hasRow()) {
            $list = $model->getAllList();
            if (!empty($list)) {
                $model->exchangeArray(current($list));
                return $model;
            }
        }
        return null;
    }

    /////////// curd:value ///////////
     
    public function offsetUnset($offset)
    {
        throw new Exception("Error Processing Request", 1);
    }
    public function offsetExists($offset)
    {            
        if (!$this->hasRow()) {//兼容 
            return $this[$offset]; 
        } else {
            return parent::offsetExists($offset);
        }
    }
    public function offsetGet($offset)
    {
        // 查分多条语句可以分别缓存，所以使用get(0)，不用limit(1,0)
        if (is_numeric($offset)) {
            return $this->get($offset);
        } elseif (class_exists($offset) && isset($offset::$pks) && isset(static::$ref)) {
            return $this->ref($offset, $offset::$pks, static::$ref[$offset]);
        } elseif (isset($this->rArgs[$offset])) {
            return $this->rArgs[$offset];
        } else {
            return $this->val($offset);
        }
    }
    public function offsetSet($offset, $value)
    {
        if (is_null($offset) && is_array($value)) {
            return $this->insert($value);
        } else {
            return $this->val($offset, $value);
        }
    }

    public function val($key, $val = null)
    {
        if ($key===null) {
            $key = current($this->tablepks);
        }
        if ($val===null) {
            // GET
            if (!$this->hasRow()) {//兼容
                $object = $this->get();
                if (isset($object)) {
                    return $object->val($key);
                }
            } 
            if(parent::offsetExists($key)) {
                return parent::offsetGet($key);
            }
        } else {
            // SET
            $this->save([$key=>$val]); 
            //兼容v4
            // if (empty($this->data->$key) || $this->data->$key != $val) {
            //     $this->dirty[$key] = $val;
            // }
            // if (isset($this->data)) {
            //     $this->data->$key = $val;
            // }
        }
    }

    public function replace($data)
    {
        $param = [];
        $str = $this->kvSQL($param, ',', $data);
        $str = "REPLACE {$this->tablename} SET {$str}";
        $param = array_merge($param, $this->wArgs);
        if (!($query = $this->session->conn->execute($str, $param))) {
            throw new \Exception("Error Processing Update", 1);
        }
        //AUTO INCREMENT
        $data = (object)$data;
        $last_id = $this->session->conn->lastInsertId();
        if (!empty($last_id) && isset($this->tablepks[0])) {
            $data->{$this->tablepks[0]}=$last_id;
        }
        // ///////// unpure /////////
        // $this->where($this->pkv($data));
        // $this->cache[$s=(string)$sql]=[ $data ];
        // ///////// unpure /////////
        return $data;
    }
    
    ////////// curd:model{table sql row} //////////

    public function insert(...$list)
    {
        $param=[];
        $sql1 = "";
        $sql2 = "";
        foreach ($list as &$arr) {
            //关联修改
            if (isset($this->parent) && $this->parent->hasRow()) { 
                foreach ($this->refpks as $k => $v) {
                    if (!$this->parent->val($v)) {
                        continue;
                    }
                    if (\in_array($k, $this->tablepks)) {
                        continue;
                    }
                    $arr[$k] = $this->parent->val($v);
                }
            }
            $sql2.=",(".substr(str_repeat(",?", count($arr)), 1).")";
            foreach ($arr as $value) {
                $param[]=$value;
            }
        }
        foreach ($list[0] as $key => $value) {
            $sql1.=",`{$key}`";
        }
        $str="INSERT INTO {$this->tablename} (".substr($sql1, 1)." )VALUES".substr($sql2, 1);
        if (!($query = $this->session->conn->execute($str, $param))) {
            throw new \Exception("Error Processing Insert Mulit", 1);
        }
        //AUTO INCREMENT
        $data = (object)last($list);
        $last_id = $this->session->conn->lastInsertId();
        if (!empty($last_id) && isset($this->tablepks[0])) {
            $data->{$this->tablepks[0]}=$last_id;
        }
        //RELATION CONDITION
        $model = $this->sql(static::class, $this->tablepks);
        if (isset($this->parent)) {
            foreach ($this->refpks as $k => $v) {
                if (isset($data->$k)) {
                    $rev[$v] = $data->$k;
                }
            }
            if (isset($rev) && $this->parent->hasRow()) {
                $this->parent->save($rev);
            }
        }
        if (isset($data->{$this->tablepks[0]})) {
            foreach ($this->tablepks as $v) {
                $pkv[$v] = $data->$v;
            }
            if (isset($pkv)) {
                $model->where($pkv);
            }
        }
        //CACHED DATA
        $model->exchangeArray($data);
        $model->sqlhash = (string)$model;
        $model->session->cache[$model->sqlhash] = [ $data ];
        return $model;
    }
    public function save($data = null)
    {
        if ($data===null) {
            return;//兼容旧版 不能报错
            // if (empty($this->dirty)) {
            //     throw new \Exception("Error Processing Request", 1);
            // }
            // if ( !$this->hasRow() ) {
            //     throw new Exception("Error Processing Request", 1);
            // }
            // $data = (array)$this->dirty;
            // $data += (array)$this->pkv($this->toArray());
            // unset($this->dirty);
        }
        //关联修改
        if ($this->hasRow()) {
            foreach ($data as $key => $value) {
                if ($this->val($key) == $data[$key]) {
                    unset($data[$key]);
                }
            }
            if (empty($data)) {
                return $this;
            }
            foreach ($data as $key => $value) {
                parent::offsetSet($key, $value );//bugfix
            }
            foreach ($this->tablepks as $k => $v) {
                $data[$v] = parent::offsetGet( $v );
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
        foreach ($this->tablepks as $value) {
            unset($data2[$value]);
        }
        $str="INSERT INTO {$this->tablename} (".substr($sql1, 1)." )VALUES".substr($sql2, 1);
        if ($sql3 = $this->kvSQL($param, ',', $data2)) {
            $str.=" ON DUPLICATE KEY UPDATE ". $sql3 ;
        }
        if (!($query = $this->session->conn->execute($str, $param))) {
            throw new \Exception("Error Processing Insert" );
        }
        //AUTO INCREMENT
        $last_id = $this->session->conn->lastInsertId();
        if (!empty($last_id) && isset($this->tablepks[0])) {
        //if (empty($data->{$this->tablepks[0]}) && !empty($last_id)) {/????
            $data->{$this->tablepks[0]}=$last_id;
        }
        //RELATION CONDITION
        $model = $this->sql(static::class, $this->tablepks);
        if (isset($this->parent)) {
            foreach ($this->refpks as $k => $v) {
                if (isset($data->$k)) {
                    $rev[$v] = $data->$k;
                }
            }
            if (isset($rev) && isset($this->parent->data)) {
                $this->parent->save($rev);
            }
        }
        if (isset($data->{$this->tablepks[0]})) {
            foreach ($this->tablepks as $v) {
                $pkv[$v] = $data->$v;
            }
            if (isset($pkv)) {
                $model->where($pkv);
            }
        }
        //CACHED DATA
        $model->exchangeArray($data);
        $model->sqlhash = (string)$model;
        $this->session->cache[$model->sqlhash] =  [ $data ];
        return $model;
    }

    public function update($data, ...$arr)
    {
        $param = [];
        $data = $this->kvSQL($param, ',', $data, $arr);
        $str = "UPDATE {$this->tablename} SET {$data} {$this->wStr}";
        $param = array_merge($param, $this->wArgs);
        if (!($query = $this->session->conn->execute($str, $param))) {
            throw new \Exception("Error Processing Update", 1);
        }
        return $query->rowCount();
    }

    public function delete($where = false, ...$args)
    {
        $sql = $this->where($where, ...$args) ;
        if (empty($sql->wStr)) {
            throw new \Exception("Require Where Column", 1);
        }
        $str="DELETE FROM {$sql->tablename} {$sql->wStr}";
        if (!($query = $this->session->conn->execute($str, $sql->wArgs))) {
            throw new \Exception("Error Processing Delete", 1);
        }
        return $query->rowCount();
    }

    //////////////// all:mixed ///////////////////
    
    public function getIterator()
    {
        //return new \ArrayIterator( $this->getAllList() );
        $all = $this->getAllList();//顺序不能换
        $model = clone $this;//先查结果再复制副本
        foreach ($all as $row) {
            $model->exchangeArray($row);
            yield $model;
        }
    }

    public function all($field = null)
    {
        $arr=[];
        if (empty($field)) {
            foreach ((clone $this) as $row) {
                $arr[]=clone $row;
            }
            return $arr;
        }
        if (\is_callable($field)) {
            foreach ((clone $this) as $row) {
                $arr[] = \call_user_func($field, $row);
            }
            return $arr;
        }
        if (\is_string($field)) {
            foreach ((clone $this)->getAllList() as $row) {
                $arr[] = $row->$field;
            }
            return $arr;
        }
        
        return $arr;//throw
    }
    public function keypair($key, $field = null)
    {
        $arr=[];
        if (empty($field)) {
            foreach ((clone $this) as $row) {
                $arr[$row->val($key)] = clone $row;
            }
            return $arr;
        }
        if (\is_callable($field)) {
            foreach ((clone $this) as $row) {
                $arr[$row->val($key)] = \call_user_func($field, $row);
            }
            return $arr;
        }
        if (\is_string($field)) {
            foreach ((clone $this)->getAllList() as $row) {
                $arr[$row->$key] = $row->$field;
            }
            return $arr;
        }
    }
    public function count($args = 1)
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
      
    public $jStr='',$gStr='', $wStr='',$lStr='',$oStr='',$fStr='*';
    public $wArgs=[];//$rArgs=[];//, $fArgs=[];//, $sArgs=[] ;

    public function getAllList($all = false)
    {
        if (!$this->hasRow()) {
            $ssql = $this->bulidSelect();
            $args = $this->bulidArgs();
            $hash = Connect::bulidSql($ssql).';'.join($args, ',');
            if (!isset($this->session->cache[$hash])) {
                $fetch = $this->session->conn->execute($ssql, $args);
                $fetch->setFetchMode(\PDO::FETCH_OBJ);
                $this->session->cache[$hash]=$fetch->fetchAll();
            }
            $this->sqlhash = $hash;
        }
        if ($all or empty($this->parent) or !$this->parent->hasRow()) {
            return $this->session->cache[ $this->sqlhash ];
        } else {
            //$list = $this->parent->getAllList();
            $filter = (object) (array)$this->parent ;//$list[$this->parent->offset];
            foreach ($this->session->cache[ $this->sqlhash ] as $index => $row) {
                foreach ($this->refpks as $k => $v) {
                    if ($row->$k!=$filter->$v) {
                        continue 2;
                    }
                }
                $arr[$index]=$row;
            }
            return $arr??[];
        }
    }
  
    public function __toString()
    {
        if (!$this->hasRow()) {
            return Connect::bulidSql($this->bulidSelect()).';'.join($this->bulidArgs(), ',');
        }
        return $this->sqlhash;
    }

    /**
     * ... LIMIT {$limit} OFFSET {$offset} ...
     * @param int $limit
     * @param int $offset
     * @return Pql
     */
    public function limit($limit, $offset = 0)
    {
        $this->lStr=" LIMIT ".intval($limit);
        if (!empty($offset)) {
            $this->lStr.=' OFFSET '.intval($offset).' ';
        }
        return $this->uncache();
    }
    /**
     * ... ORDER {$order} ...
     * @param string $order
     * @param array ...$arr
     * @return Pql
     */
    public function order(string $order)
    {
        $this->oStr=" ORDER BY ".$order;
        return $this->uncache();
    }
    /**
     * SELECT {$fileds} FROM ...
     * @param string|array $fields
     * @return Pql
     */
    public function field($fields)
    {
        if (is_array($fields)) {
            $this->fStr=join($fields, ',');
        } else {
            $this->fStr=$fields;
        }
        return $this->uncache();
    }
     
    /**
     * ... WHERE {$w} ...
     * @param string|array $w
     * @param array ...$arr
     * @return Pql
     */
    public function where($w, ...$arr)
    {
        if (!empty($w)) {
            $this->wArgs=[];
            $this->wStr=" WHERE (".$this->kvSQL($this->wArgs, ' AND ', $w, $arr).")";
        }
        return $this->uncache();
    }
    public function whereAnd($w, ...$arr)
    {
        if (!empty($w)) {
            $this->wStr.=empty($this->wStr)?" WHERE ":" AND ";
            $this->wStr.="(".$this->kvSQL($this->wArgs, ' AND ', $w, $arr).")";
        }
        return $this->uncache();
    }
    public function whereOr($w, ...$arr)
    {
        if (!empty($w)) {
            $this->wStr.=empty($this->wStr)?" WHERE ":" OR ";
            $this->wStr.="(".$this->kvSQL($this->wArgs, ' OR ', $w, $arr).")";
        }
        return $this->uncache();
    }


    public function find(...$pkv)
    {
        if (is_array($pkv[0] && empty($pkv[0][0]))) {
            $arr = $pkv[0];
        } else {
            $arr = array_combine($this->tablepks, $pkv);
        }
        $this->rArgs=$arr;
        return $this->where($arr)->uncache();
    }
    
    /**
     * ... FROM [TABLE] JOIN {$str} ...
     * @param string  $str
     * @return Pql
     */
    public function join($str)
    {
        $this->jStr=" $str";
        return $this->uncache();
    }
    /**
     * ... GROUP BY {$str} ...
     * @param string  $str
     * @return Pql
     */
    public function group($str)
    {
        $this->gStr=" GROUP BY $str";
        return $this->uncache();
    }

    
        
    public function kvSQL(&$param, $jtag = ' AND ', $arr, $attr = null, $sql = '')
    {
        if (is_array($arr)) {
            foreach ($arr as $key => $v) {
                if (strstr($key, ',')) {
                    $key="($key)";
                } 
                if (isset($v->session)) { 
                    $sql.="{$jtag}{$key} in (".$v->bulidSelect().")";
                    $param=array_merge($param, $v->bulidArgs());
                    continue;
                }
                if (is_array($v)) {
                    if (count($v)>1) {
                        $str= substr(str_repeat(",?", count($v)), 1);
                        $sql.="{$jtag}{$key} in ($str) " ;
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
    public function bulidArgs()
    {
        return $this->wArgs;
    }
    public function bulidSelect()
    {
        return "SELECT {$this->fStr} FROM {$this->tablename}{$this->jStr} {$this->wStr}{$this->gStr} {$this->oStr} {$this->lStr}";
    }
    public function pkv($data)
    {
        foreach ($this->tablepks as $k) {
            $arr[$k]=$data[$k];
        }
        return $arr;
    }
    
    public function uncache()
    {
        unset($this->sqlhash);// = null;
        return $this;
    }

    public function hasRow()
    {
        return isset($this->sqlhash);
    }
}
