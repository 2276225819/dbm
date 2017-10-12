<?php namespace dbm;

/**
 * dbm v5
 **/
class Collection extends \ArrayObject
{
    use CollectionPrivate,CollectionCompatible;
    public $session;#{cache:{sqlhash:[]}}
    public $tablename ,$tablepks ;//,$refpks=[];//$parent={};//*ArrayObject*storage

 
    /////////// query:model{table} ///////////
    
    /**
     * @param [type] $table
     * @param [type] $pks
     * @return \dbm\Collection
     */
    public function sql($table, $pks = null)
    {
        return static::new($table, $pks, $this->session);
    }
    /**
     * @param [type] $table
     * @param array $pks
     * @param array $ref
     * @return \dbm\Collection
     */
    public function ref($table, $pks = [], $ref = [])
    {
        $model = static::new($table, $pks, $this->session );
        if (empty($ref) && isset(static::$ref[$table])) {
            $ref = static::$ref[$table];
        }
        if ($this->isRow()) {
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
    /**
     * @param [type] $offset
     * @return \dbm\Collection
     */
    public function get($offset = 0)
    {
        $list = array_values( $this->getAllList() );
        if (!empty($list[$offset])) {
            $this->exchangeArray($list[$offset]);
            return $this;
        } else {
            return null;
        }
    }
    public function first($offset = 0)
    {
        return ( clone $this )->limit(1, $offset)->get();
    }
    /*
     * @param [type] ...$pkv
     * @return \dbm\Collection
     */
    public function find(...$pkv)
    {
        if (is_array($pkv[0] && empty($pkv[0][0]))) {
            $arr = $pkv[0];
        } else {
            $arr = array_combine($this->tablepks, $pkv);
        }
        $model = (clone $this);
        $model->rArgs = $arr;
        return $model->where($arr)->limit(1)->get();
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
            foreach ($this as $row) {
                $arr[]=clone $row;
            }
            return $arr;
        }
        if (\is_callable($field)) {
            foreach ($this as $row) {
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
            foreach ($this as $row) {
                $arr[$row->val($key)] = clone $row;
            }
            return $arr;
        }
        if (\is_callable($field)) {
            foreach ($this as $row) {
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



    /**
     * ... LIMIT {$limit} OFFSET {$offset} ...
     * @param int $limit
     * @param int $offset
     * @return \dbm\Collection
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
     * @return \dbm\Collection
     */
    public function order(string $order)
    {
        $this->oStr=" ORDER BY ".$order;
        return $this->uncache();
    }
    /**
     * SELECT {$fileds} FROM ...
     * @param string|array $fields
     * @return \dbm\Collection
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
     * @return \dbm\Collection
     */
    public function where($w, ...$arr)
    {
        if (!empty($w)) {
            $this->wArgs=[];
            $this->wStr=" WHERE (".$this->kvSQL($this->wArgs, ' AND ', $w, $arr).")";
        }
        return $this->uncache();
    }
    /**
     * ... AND {$w} ...
     * @param string|array $w
     * @param array ...$arr
     * @return \dbm\Collection
     */
    public function whereAnd($w, ...$arr)
    {
        if (!empty($w)) {
            $this->wStr.=empty($this->wStr)?" WHERE ":" AND ";
            $this->wStr.="(".$this->kvSQL($this->wArgs, ' AND ', $w, $arr).")";
        }
        return $this->uncache();
    }
    /**
     * ... OR {$w} ...
     * @param string|array $w
     * @param array ...$arr
     * @return \dbm\Collection
     */
    public function whereOr($w, ...$arr)
    {
        if (!empty($w)) {
            $this->wStr.=empty($this->wStr)?" WHERE ":" OR ";
            $this->wStr.="(".$this->kvSQL($this->wArgs, ' OR ', $w, $arr).")";
        }
        return $this->uncache();
    }


    /**
     * ... FROM [TABLE] JOIN {$str} ...
     * @param string  $str
     * @return \dbm\Collection
     */
    public function join($str)
    {
        $this->jStr=" $str";
        return $this->uncache();
    }
    /**
     * ... GROUP BY {$str} ...
     * @param string  $str
     * @return \dbm\Collection
     */
    public function group($str)
    {
        $this->gStr=" GROUP BY $str";
        return $this->uncache();
    }
    /////////// curd:value ///////////
     

    
    public function toArray()
    {
        return (array)$this;
    }

    public function val($key, $val = null)
    {
        if ($key===null) {
            $key = current($this->tablepks);
        }
        if ($val===null) {
            // GET
            if (!$this->isRow()) {//兼容:默认读第一行
                $object = $this->get();
                if (isset($object)) {
                    return $object->val($key);
                }
            }
            if (parent::offsetExists($key)) {
                return parent::offsetGet($key);
            }
        } else {
            // SET
            $this->save([$key=>$val]);
        }
    }

    public function replace($data = null)
    {
        if (empty($data)) {
            return $this;//兼容:旧版不报错
        }
        list($model,$data) = $this->beginModel($data);

        //关联修改
        if (isset($this->parent) && $this->parent->isRow()) {
            $data = $data + $this->relaval((array)$this->parent);
        }
        $param = [];
        $str = $model->kvSQL($param, ',', $data);
        $str = "REPLACE {$model->tablename} SET {$str}";
        //$param = array_merge($param, $model->wArgs);
        if (!($query = $model->session->conn->execute($str, $param))) {
            throw new \Exception("Error Processing Update", 1);
        }
        return $this->endModel($model, (object)$data);
    }
 
    ////////// curd:model{table sql row} //////////

    public function insert(...$list)
    {
        if (empty($list[0])) {
            return $this;//兼容:旧版不报错
        }
        list($model,$data) = $this->beginModel();

        //关联修改
        if (isset($this->parent) && $this->parent->isRow()) {
            $parent_data = (array)$this->parent;
            foreach ($this->refpks as $k => $v) {
                if (in_array($k, $this->tablepks)) {
                    unset($parent_data[$v]);//忽略外键插入
                }
            }
        }
        $param = [];
        $sql1 = "";
        $sql2 = "";
        foreach ($list as &$arr) {
            if (isset($parent_data)) {
                $arr = $arr + $this->relaval($parent_data);  //关联修改
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
        return $this->endModel($model, (object)last($list), true );
    }
    public function save($data = null)
    {
        if (empty($data)) {
            return $this;//兼容:旧版不报错
        }
        list($model,$data) = $this->beginModel($data);

        //兼容:自身修改(集合指针行)
        if ($this->isRow()) {
            foreach ($data as $key => $value) {
                parent::offsetSet($key, $value );//bugfix
            }
            foreach ($this->tablepks as $k => $v) {
                $data[$v] = parent::offsetGet( $v );
            }
        }
        //关联修改
        if (isset($this->parent) && $this->parent->isRow()) {
            $data = $data + $this->relaval((array)$this->parent);
        }

        $param = [];
        $sql1 = '';
        $sql2 = ",(".substr(str_repeat(",?", count($data)), 1).")";
        foreach ($data as $key => $value) {
            $sql1.=",`{$key}`";
            $param[]=$value;
        }
        $data2 = $data;
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
        return $this->endModel($model, (object)$data);
    }

    public function update($data, ...$arr)
    {
        $model = clone $this;
        if (empty($model->wStr)) {
            throw new \Exception("Require Where Column", 1);
        }
        $param = [];
        $data = $model->kvSQL($param, ',', $data, $arr);
        $str = "UPDATE {$model->tablename} SET {$data} {$model->wStr}";
        $param = array_merge($param, $model->wArgs);
        if (!($query = $model->session->conn->execute($str, $param))) {
            throw new \Exception("Error Processing Update", 1);
        }
        return $query->rowCount();
    }

    public function delete($where = false, ...$args)
    {
        $model = clone $this;
        if (!empty($model->lStr)) {
            $model->get(); //Bugfix
            $model->lStr='';
        }
         //自身修改
        if ($model->isRow() && empty($model->wStr)) {
            foreach ($model->tablepks as $k => $v) {
                $data[$v] = $model->all($v);// parent::offsetGet( $v );
            }
            $model->whereAnd($data);
        }
        $model->whereAnd($where, ...$args);
        
        if (empty($model->wStr)) {
            throw new \Exception("Require Where Column", 1);
        }
        $str="DELETE FROM {$model->tablename} {$model->wStr}";
        if (!($query = $model->session->conn->execute($str, $model->wArgs))) {
            throw new \Exception("Error Processing Delete", 1);
        }
        return $query->rowCount();
    }
}
