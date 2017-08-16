<?php namespace dbm;

class Model implements \IteratorAggregate, \ArrayAccess, \JsonSerializable
{
    const Model=true;

    use ModelAccess;
    
    /**
     * @var Pql
     */
    public $sql;

    /////////////model///////////////

    /**
     * $sql->get()       as Model{count=1}
     *
     * $sql->get(OFFSET) as Model{count=1}
     *
     * @param number|null $offset
     * @return \dbm\Model
     */
    public function get($offset = null)
    {
        if (is_numeric($offset)) {
            $this->limit(1, $offset);
            $offset = 0;
        } else {
            $offset = 0;
        }
        $list = $this->session->select($this->sql);
        if (empty($list[$offset])) {
            return null;
        }
        return new static($this->session,clone $this->sql,$list[$offset]);
    } 
    /**
     * $sql->load(...ID) as Model{count=1}
     *
     * @param mixed[] ...$pkv
     * @return \dbm\Model
     */
    public function load(...$pkv)
    { 
        $sql = (clone $this->sql)->find(...$pkv);
        $list = $this->session->select($sql);
        if (empty($list[0])) {
            return null;
        }
        return new static($this->session,$this->sql,$list[0]);
    }
    /**
     * $sql->val() as pkv
     *
     * $sql->val(FILED) as mixed
     *
     * $sql->val(FIELD,VALUE) as VOID
     *
     * @param string $field
     * @return mixed
     */
    public function val($field = null, $value = null)
    {
        if ($field===null) {
            $field = $this->sql->pks[0];
        }
        // if ($value===null) {
        //     //BUG
        //     $data = array_merge(
        //         (array)($this->data??[]),
        //         (array)($this->session->select($this->sql)[0]??[])
        //     ) ;   
        //     return $data[$field]??null;
        // }         
        if ($value===null) {
            $data = [];
            $data+=(array)($this->data??[]);
            $data+=(array)($this->session->select($this->sql)[0]??[]); 
            return $data[$field]??null;
        } 
        if(!isset($this->data->$field) || $this->data->$field!=$value) {
            if(isset($this->data)){
                $this->data->$field = $value; 
            }
            $this->dirty[$field]=$value;
        }
    } 
    /**
     * @param string $model
     * @param array $pks
     * @param array $ref
     * @return \dbm\Model
     */
    public function ref($table, $pks = [], $ref = [])
    {
        $model = static::byName($this->session,$table, $pks);
        if (empty($ref) && isset(static::$ref[$table])){
            $ref = static::$ref[$table];  
        } 
        if (empty($this->data)) {
            $keys=join(array_keys($ref), ',');
            $query = $this->sql->field(array_values($ref));
            $model->sql->and([$keys=>$query]);
            $_ref = array_flip($ref);
            foreach ($this->sql->rArgs as $k => $v) {
                if (isset($_ref[$k])) {
                    $model->sql->rArgs[$_ref[$k]]=$v;
                }
            }
        } else {
            $thisdata = $this->session->select($this->sql, true);
            foreach ($ref as $k => $f) {
                foreach ($thisdata as $row) {
                    //if (!empty($row[$f])) {
                        $s[$k][]=$row->$f??null;
                    //}
                }
                if (isset($s[$k])) {
                    $s[$k] = array_unique($s[$k]);
                    sort($s[$k]);
                }
            }
            if (isset($s)) {
                $model->sql->and($s);
            }
            foreach ($ref as $k => $v) {
                if ($val=$this->val($v)) {
                    $model->sql->rArgs[$k]=$val;
                }
            }
            if (!empty($thisdata)) {
                do {
                    foreach ($ref as $k => $v) {
                        if (!in_array($k, $model->sql->pks)) {
                            break 2;
                        }
                    }
                    $model->sql->rmodel = $this;
                    $model->sql->rref = $ref;
                } while (false);
            }
        }

        
        return $model;
    }
    /** 
     * @param [type] $model
     * @param [type] $pks
     * @return \dbm\Model
     */
    public function sql($model, $pks = null){
        return self::byName($this->session,$model,$pks);
    } 
    
    /////////////collection/////////////// 

    /**
     * [ $key, $key... ] | [ Model, Model... ]
     * @param string|callable $field
     * @return \dbm\Model[]
     */
    public function all($field = null)
    {
        $arr=[];
        switch(true){
            case \is_callable($field):
                foreach ($this as $row) {
                    $arr[]=\call_user_func($field,clone $row);
                }
                break;
            case \is_string($field):
                foreach ($this as $row) {
                    $arr[]=$row->data->$field;
                }
                break;
            default: 
                foreach ($this as $row) {
                    $arr[]=clone $row;
                }
        } 
        return $arr;
    } 
    /**
     * [ $key=>Row, $key=>Row... ] | [ $key => $val, $key => $val... ]
     * @param string $key
     * @param string $field
     * @return \dbm\Model[]
     */
    public function keypair($key = null, $field = null)
    {
        $arr=[];
        if (empty($key)) {
            $key = $this->sql->pks[0];
        }
        if ($this->sql->fStr!='*') {
            $this->sql->fStr.=",$key as __KEY__";
            $key = "__KEY__";
        } 
        switch(true){
            case \is_callable($field):
                foreach ($this as $row) {
                    $kstr = $row->data->$key;
                    if ($key=='__KEY__') {
                        unset($row->data->$key);
                    }
                    $arr[$kstr]=\call_user_func($field,clone $row);
                }
                break;
            case \is_string($field):
                foreach ($this as $row) {
                    $kstr = $row->data->$key;
                    if ($key=='__KEY__') {
                        unset($row->data->$key);
                    }
                    $arr[$kstr]=$row->data->$field;
                }
                break;
            default:  
                foreach ($this as $row) {  
                    $kstr = $row->data->$key;
                    if ($key=='__KEY__') {
                        unset($row->data->$key);
                    }
                    $arr[$kstr]=clone $row;
                }
        } 
        return $arr; 
    }
 
    /////////////curd/////////////

    /**
     * Row{count=1}
     * @param array $data
     * @return \dbm\Model
     */
    public function insert($arr,$pks=[])
    {
        $sql = $this->sql;
        $sql->pks=\array_merge($sql->pks, (array)$pks);
        $data = (array) $this->session->insert($sql, $arr); 
        if (isset($sql->rmodel)) {
            $model = clone $sql->rmodel;
            unset($sql->rArgs); 
            foreach ($sql->rref as $i => $k) {
                $model[$k]=$data[$i];
            } 
            $model->save();
        }
        return new static($this->session,clone $sql,$data);
    } 
    /**
     * RowCount
     * @param array $data
     * @param array ...$arr
     * @return int
     */
    public function update($data, ...$arr)
    {
        $sql = $this->sql;
        if (empty($sql->wStr)) {
            throw new \Exception("Require Where Column", 1);
        }
        $count = $this->session->update($sql, $data, ...$arr);
        if (isset($this->session->cache[$s=(string)$sql])) {
            foreach ($this->session->cache[$s] as $value) {
                foreach ($data as $k => $v) { 
                    $value->$k=$v;
                } 
            } 
        }   
        return $count;
    } 
    /**
     * RowCount
     * @return int
     */
    public function delete($force = false)
    {
        $sql = $this->sql;
        if (!$force && empty($sql->wStr)) {
            throw new \Exception("Require Where Column", 1);
        }
        $count = $this->session->delete($sql);
        return $count;
    } 
    /**
     * RowCount
     * @return int
     */
    public function replace($data,...$arr)
    { 
        $sql = $this->sql;

        $param = [];
        $data = $sql->kvSQL($param, ',', $data, $arr);
        $param = array_merge($param, $sql->wArgs);
		$str="REPLACE {$sql->table} SET {$data}";
        if (!($query = $this->session->conn->execute($str, $param))) {
            throw new \Exception("Error Processing Replace", 1);
        }
        return $query->rowCount();
    }

    /////////////sql///////////////
    
    /**
     * @return \dbm\Model
     */
    public function where($w, ...$arr)
    {
        $this->sql->where($w, ...$arr);
        return $this;
    }
    /**
     * @return \dbm\Model
     */
    public function whereAnd($w, ...$arr)
    {
        $this->sql->and($w, ...$arr);
        return $this;
    }
    /**
     * @return \dbm\Model
     */
    public function whereOr($w, ...$arr)
    {
        $this->sql->or($w, ...$arr);
        return $this;
    }
    /**
     * Sql
     * @param int $limit
     * @param int $offset
     * @return \dbm\Model
     */
    public function limit($limit, $offset = 0)
    {
        $this->sql->limit($limit, $offset);
        return $this;
    }
    /**
     * Sql
     * @param mixed[] ...$pkv
     * @return \dbm\Model
     */
    public function find(...$pkv)
    {
        $this->sql->find(...$pkv);
        return $this;
    } 
    /**
     * @return \dbm\Model
     */
    public function order($order, ...$arr)
    {
        $this->sql->order($order, ...$arr);
        return $this;
    }
    /**
     * @return \dbm\Model
     */
    public function field($arr)
    {
        $this->sql->field($arr);
        return $this;
    }

}
