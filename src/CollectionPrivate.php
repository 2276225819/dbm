<?php namespace dbm;

trait CollectionPrivate
{
    public static function new($table, $pks, $session)
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
            return $this->save([$offset=>$value]); 
        }
    }
    
    //////////////// all:mixed ///////////////////

    public function getIterator()
    {
        //return new \ArrayIterator( $this->getAllList() );
        $model = clone $this; 
        $all = $model->getAllList(); 
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
            $hash = ($ssql).';'.join($args, ',');
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
        return Connect::bulidSql($this->bulidSelect()).';'.join($this->bulidArgs(), ','); 
    }



    private function relaval($pdata){ 
        foreach ($this->refpks as $k => $v) { 
            if(isset($pdata[$v])){ 
                if (!\in_array($k, $this->tablepks)) {
                    $arr[$k] = $pdata[$v];
                    continue;
                }
                if (!\in_array($v, $this->tablepks)) {
                    $arr[$k] = $pdata[$v];
                }
            }
        } 
        return $arr??[];
    } 
    private function beginModel($data=null){
        //组合插入
        if ($data instanceof self) {
            $model = $data;
            $model->session  = $this->session;
            $model->tablename= $this->tablename;
            $model->tablepks = $this->tablepks;
            $data = (array)$data;
            // foreach($model->getArrayCopy() as $key=>$row){
            //     if($row instanceof self){
            //         continue;
            //     }
            //     if(is_array($row)){
            //         $array[$key] = $row;
            //         continue;
            //     }
            //     $data[$key] = $row;
            // }
        } else {
            $model = self::new($this->tablename, $this->tablepks,$this->session);
        } 
        return [$model,$data];
    }
    private function endModel($model, $row ,$insert = false){
        //AUTO INCREMENT 
        $last_id = $model->session->conn->lastInsertId();
        if (!empty($last_id) && isset($model->tablepks[0])) {
            $row->{$model->tablepks[0]}=$last_id;
        } 
        if (isset($model->tablepks[0]) && isset($row->{$model->tablepks[0]})) {
            foreach ($model->tablepks as $v) {
                $pkv[$v] = $row->$v;
            }
            if (isset($pkv)) {
                $model->where($pkv);
            }
        } 
        //RELATION CONDITION
        if ($insert && isset($this->parent)) {
            foreach ($this->refpks as $k => $v) {
                if (isset($row->$k)) {
                    $data[$v] = $row->$k;
                }
            }
            if (isset($data) && $this->parent->isRow()) {
                foreach ($data as $key => $value) {
                    if ($this->parent->val($key) == $data[$key]) {
                        unset($data[$key]);
                    }
                } 
                $this->parent->save($data); 
            }
        } 
        //CACHED DATA
        $model->exchangeArray($row);
        $model->sqlhash = ($model->bulidSelect()).';'.join($model->bulidArgs(), ',');
        $model->session->cache[$model->sqlhash] = [ $row ];
        

        // //组合插入
        // if(isset($value)){
        //     foreach($value as $key=>$row){
        //         $model[$key]->replace($row);
        //     }
        // }
        // if(isset($array)){
        //     foreach($array as $key=>$row){
        //         foreach ($row as $v) {
        //             $model[$key][] = $v;
        //         }
        //     }
        // }
        return $model;
    }
    
    // private function execute($sql,$param){ 
    //     return $this->session->conn->execute($sql, $param);
    // }

        
    private function kvSQL(&$param, $jtag = ' AND ', $arr, $attr = null, $sql = '')
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
    private function bulidArgs()
    {
        return $this->wArgs;
    }
    private function bulidSelect()
    {
        return "SELECT {$this->fStr} FROM {$this->tablename}{$this->jStr} {$this->wStr}{$this->gStr} {$this->oStr} {$this->lStr}";
    }
    private function pkv($data)
    {
        foreach ($this->tablepks as $k) {
            $arr[$k]=$data[$k];
        }
        return $arr;
    }
    
    private function uncache()
    {
        unset($this->sqlhash);// = null;
        return $this;
    }

    private function isRow()
    {
        return isset($this->sqlhash);
    }
    
}
