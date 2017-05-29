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
    function get($offset = null)
    {
        if (is_numeric($offset)) {
            $this->limit(1, $offset);
            $offset = 0;
        } else {
            $offset = 0;
        }
        $list = Session::$instance->select($this->sql);
        if (empty($list[$offset])) {
            return null;
        }
        return new static($this->sql,$list[$offset]);
    }
   
    /**
     * $sql->load(...ID) as Model{count=1}
     *
     * @param mixed[] ...$pkv
     * @return \dbm\Model
     */
    function load(...$pkv)
    { 
        $sql = (clone $this->sql)->find(...$pkv);
        $list = Session::$instance->select($sql);
        if (empty($list[0])) {
            return null;
        }
        return new static($this->sql,$list[0]);
    }
    /**
     * $sql->val() as array
     *
     * $sql->val(FILED) as mixed
     *
     * $sql->val(FIELD,VALUE) as VALUE
     *
     * @param string $field
     * @return mixed
     */
    function val($field = null, $value = null)
    {
        if ($field===null) {
            $data = $this->data+Session::$instance->select($this->sql)[0]; 
            return $data;
        }
        if ($value===null) {
            $data = $this->data+Session::$instance->select($this->sql)[0]; 
            return $data[$field]??null;
        } 
        if(!isset($this->data[$field]) || $this->data[$field]!=$value) {
            $this->data[$field] = $value;
            $this->dirty[$field]= $value;
        }
    }
    
    /**
     * @param string $model
     * @param array $pks
     * @param array $ref
     * @return \dbm\Model
     */
    function ref($model, $pks = null, $ref = null)
    {
        $model = Model::byName($model, $pks);
        if (empty($ref) && isset(static::$ref[get_class($model)])) {
            $ref = static::$ref[get_class($model)];
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
            $thisdata = Session::$instance->select($this->sql, true);
            foreach ($ref as $k => $f) {
                foreach ($thisdata as $row) {
                    if (!empty($row[$f])) {
                        $s[$k][]=$row[$f];
                    }
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
                        if (!in_array($k, $model::$pks)) {
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
     * [ $key, $key... ] | [ $pk, $pk...]
     * @param string $field
     * @return \dbm\Model[]
     */
    function all($field = null)
    {
        $arr = array();
        foreach ($this as $row) {
            if (empty($field)) {
                $arr[]=clone $row;
            } else {
                $arr[]=$row->data[$field];
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
    function keypair($key = null, $field = null)
    {
        $arr=[];
        if (empty($key)) {
            $key = $this->sql->pks[0];
        }
        if ($this->sql->fStr!='*') {
            $this->sql->fStr.=",$key as __KEY__";
            $key = "__KEY__";
        }
        foreach ($this as $row) {
            $kstr = $row->data[$key];
            if ($key=='__KEY__') {
                unset($row->data[$key]);
            }
            if (empty($field)) {
                $arr[$kstr]=clone $row;
            } else {
                $arr[$kstr]=$row->data[$field];
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
    function insert($arr)
    {
        $sql = $this->sql;
        $data = Session::$instance->insert($sql, $arr); 
        if (isset($sql->rmodel)) {
            unset($sql->rArgs); 
            foreach ($sql->rref as $i => $k) {
                $sql->rmodel[$k]=$data[$i];
            } 
            $sql->rmodel->save();
        }
        return new static(clone $sql,$data);
    }
    /**
     * RowCount
     * @param array $data
     * @param array ...$arr
     * @return int
     */
    function update($data, ...$arr)
    {
        $sql = $this->sql;
        if (empty($sql->wStr)) {
            throw new \Exception("Require Where Column", 1);
        }
        $count = Session::$instance->update($sql, $data, ...$arr);
        if ($row = &Session::$instance->cache[$s=(string)$sql]) {
            foreach ($row as &$value) {
                $value = array_merge($value, $data);
            }
        }
        return $count;
    }
    /**
     * RowCount
     * @param array $list
     * @return int
     */
    function insertMulit($list)
    {
        if (!count($list)) {
            throw new \Exception("Error Muilt Column", 1);
        }
        $count = Session::$instance->insertMulit($this->sql, $list);
        return $count;
    }

    /**
     * RowCount
     * @return int
     */
    function delete($force = false)
    {
        $sql = $this->sql;
        if (!$force && empty($sql->wStr)) {
            throw new \Exception("Require Where Column", 1);
        }
        $count = Session::$instance->delete($sql);
        return $count;
    }



    /**
     * self
     * @param array $dirty
     * @return void
     */
    function save($dirty = [])
    {
        $dirty += $this->dirty; 
        if (!count($dirty)) {
            throw new \Exception("Require Change Column", 1);
        }
        if(!empty($this->sql->rArgs)){
            $this->where($this->sql->rArgs);
        }
        if(empty($this->sql->wStr)){
            $row = $this->insert($dirty);
            $this->data = $row->data;
            $this->sql = $row->sql;
        }else{
            $this->update($dirty); 
        }
        $this->dirty=[];
        return $this;
        //return true;
        //return $this->dirty; 
    }

    /**
     * Model{count=1}
     * @param array $data
     * @return \dbm\Model
     */
    function set($data)
    { 
        $data += $this->sql->rArgs;
        foreach ($this->sql->pks as $key) {
            if (isset($data[$key])) {
                $where[$key]=$data[$key];
            }
        }
        if (isset($where)) {
            if ($row = (clone $this)->where($where)->get()) {
                foreach ($data as $key => $value) {
                    $row[$key]=$value;
                }
                $row->save();
                return $row;
            }
        }
        return $this->insert($data);
    }
    /////////////sql///////////////
    
    /**
     * @return \dbm\Model
     */
    function where($w, ...$arr)
    {
        $this->sql->where($w, ...$arr);
        return $this;
    }
    /**
     * @return \dbm\Model
     */
    function whereAnd($w, ...$arr)
    {
        $this->sql->and($w, ...$arr);
        return $this;
    }
    /**
     * @return \dbm\Model
     */
    function whereOr($w, ...$arr)
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
    function limit($limit, $offset = 0)
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
    function order($order, ...$arr)
    {
        $this->sql->order($order, ...$arr);
        return $this;
    }
    /**
     * @return \dbm\Model
     */
    function field($arr)
    {
        $this->sql->field($arr);
        return $this;
    }
    /**
     * @return \dbm\Model
     */
    function join($str)
    {
        $this->sql->join($str);
        return $this;
    }
}
