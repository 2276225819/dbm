<?php namespace dbm;

trait CollectionPrivate
{
    static function new($table, $pks, $session)
    {
        if (class_exists($table) && isset($table::$table)) {
            $model = new $table;
            $model->session = $session;
            $model->tablepks = (array)$pks+(array)$table::$pks;
            $model->tablename = $table::$table;
        } else {
            $model = new static;
            $model->session = $session;
            $model->tablepks = (array)$pks;
            $model->tablename = $table;
        }
        return $model->uncache();
    }
    /////////// curd:value ///////////
     
    public function offsetUnset($offset)
    {
        throw new Exception("Error Processing Request", 1);
    }
    public function offsetExists($offset)
    {
        if (!$this->isRow()) {//兼容
            return $this[$offset];
        } else {
            return parent::offsetExists($offset);
        }
    }
    public function offsetGet($offset)
    { 
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
        if ( is_null($offset) ) {
            return $this->replace($value);
        } elseif (class_exists($offset) && isset($offset::$pks) && isset(static::$ref)) {
            return $this->ref($offset, $offset::$pks, static::$ref[$offset])->replace($value);
        } else {
            return $this->val($offset, $value);
        }
    }
    
    //////////////// all:mixed ///////////////////

    public function getIterator()
    {
        //return new \ArrayIterator( $this->getAllList() );
        $model = clone $this;//先查结果再复制副本
        $all = $this->getAllList();//顺序不能换
        foreach ($all as $row) {
            $model->exchangeArray($row);
            yield $model;
        }
    }

    public function getAllList($all = false)
    {
        if (!$this->isRow()) {
            if (isset($this->parent)) {
                $this->lStr='';//bug: 1.sqlgetex.phpt
            }
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
        if ($all or empty($this->parent) or !$this->parent->isRow()) {
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
        if (!$this->isRow()) {
            return Connect::bulidSql($this->bulidSelect()).';'.join($this->bulidArgs(), ',');
        }
        return $this->sqlhash;
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

    public function isRow()
    {
        return isset($this->sqlhash);
    }
}
