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
     * @param number|null $offset 
     * @return Model
     */
    function get($offset = null)
    {
        if (is_numeric($offset)) {
            $this->limit(1, $offset);
            $this->list=null;
            $offset = 0;
        }else{
            $offset = 0;
        }
        if (empty($this->list)) {
            $this->list = Session::$instance->select($this->sql);
        }
        if (empty($this->list[$offset])) {
            return null;
        }
        $model = new static($this->sql);
        $model->list = [$this->list[$offset]];
        foreach ($this->sql->pks as $key) {
            $model->sql->rArgs[$key]=$this->list[$offset][$key];
        }
        return $model;
    } 
   
    /** 
     * @param mixed[] ...$pkv
     * @return Model
     */
    function load(...$pkv)
    {
        if (!isset($this->list)) {
            $sql = $this->find(...$pkv)->sql;
            $this->list = Session::$instance->select($sql);
        } 
        return $this;
    } 
 
    /**
     * $sql->val(FILED) as mixed
     * @param string $field
     * @return mixed
     */
    function val($field, $val = null)
    {
        if (isset($val)) {
            return $this->dirty[$field]=$val;
        } else {
            if (!isset($this->list)) {
                $this->list = Session::$instance->select($this->sql);
            }
            return $this->list[0][$field];
        }
    }
    
    /** 
     * @param string $model
     * @param array $pks
     * @param array $ref
     * @return Model
     */
    function ref($model, $pks = null, $ref = null)
    { 
        $model = Model::byName($model,$pks);  
        if(empty($ref) && isset(static::$ref[get_class($model)])){
            $ref = static::$ref[get_class($model)]; 
        } 
        if (!isset($this->list)) {
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
            $s=[];
            foreach ($ref as $k => $f) {
                foreach ($thisdata as $row) {
                    $s[$k][]=$row[$f];
                }
                if(isset($s[$k])){
                    $s[$k] = array_unique($s[$k]); 
                }
            }
            $model->sql->and($s);
            foreach ($ref as $k => $v) {
                $model->sql->rArgs[$k]=$this->val($v);
            }
        }
        do{
            foreach ($ref as $k => $v) { 
                if(!in_array($k,$model::$pks))
                    break 2;
            } 
            $model->sql->rsql=$this->sql;
            $model->sql->rref=$ref; 
        }while(false); 
        
        return $model;
    }
    /** 
     * [ $key, $key... ] | [ $pk, $pk...]
     * @param string $field
     * @return Model[] 
     */
    function all($field=null)
    {  
        $arr=[];
        foreach ($this as $row) { 
            if(empty($field))  {
                $arr[]=clone $row; 
            }
            else if(isset($row->list[0][$field])){
                $arr[]=$row->list[0][$field];
            }
        } 
        return $arr;
    }

    /**
     * [ $key=>Row, $key=>Row... ] | [ $key => $val, $key => $val... ]
     * @param string $key
     * @param string $field
     * @return Model[] 
     */
    function keypair($key=null, $field = null)
    { 
        $arr=[];
        if(empty($key)){
            $key = $this->sql->pks[0];
        }
        $this->sql->fStr.=",$key as __KEY__";
        foreach ($this as $row) { 
            $kstr = $row->list[0]['__KEY__'];
            unset($row->list[0]['__KEY__']);
            if(empty($field))  {
                $arr[$kstr]=clone $row;
            }
            elseif(isset($row->list[0][$field])){
                $arr[$kstr]=$row->list[0][$field];
            }
        } 
        return $arr; 
    }

    /////////////curd/////////////

    /**
     * Row
     * @param array $data
     * @param int $auto_increment_key
     * @return Model
     */
    function insert($arr)
    {
        $arr = Session::$instance->insert($this->sql, $arr);
        // if (isset($this->rModel)) {
        // 	foreach ($this->rref as $i => $k) {
        // 		$this->rModel[$k]=$data[$i];
        // 	}
        // 	$this->rModel->save();
        // }
        $row = new self($this->sql,Session::$instance);
        $pk = $this->sql->pks[0];
        $row->where([$pk=>$arr[$pk]]);
        $row->list = [$arr];
        return $row;
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
     * @param array $data
     * @param array ...$arr
     * @return int
     */
    function update($arr)
    {
        if (empty($this->sql->wStr)) {
            throw new \Exception("Require Where Column", 1);
        }
        $count = Session::$instance->update($this->sql, $arr);
        return $count;
    }
    /**
     * RowCount
     * @return int
     */
    function delete($force = false)
    {
        if (!$force && empty($this->sql->wStr)) {
            throw new \Exception("Require Where Column", 1);
        }
        $count = Session::$instance->delete($this->sql);
        return $count;
    }




    function save($dirty = null)
    {
        if (isset($dirty)) { 
            $this->dirty= array_merge($this->dirty,$dirty);
        }
        if (empty($this->dirty)) {
            throw new \Exception("Require Change Column", 1);
        }
        if (count($this->sql->rArgs)) {
            $this->where($this->sql->rArgs);
        }
        if (empty($this->sql->wStr)) {
            $row = $this->insert($this->dirty);
            $this->list=$row->list;
            return $this;
        } else {
            $this->update($this->dirty);
            return $this;
        }
    } 
    function set($arr)
    {
        $data = array_merge($this->sql->rArgs, $arr);
        foreach ($this->sql->pks as $key) {
            if (isset($data[$key]) && in_array($key, $this->sql->pks)) {
                $where[$key]=$data[$key];
            }
        }
        if (isset($where)) {
            if ($row = $this->where($where)->get()) {
                foreach ($data as $key => $value) {
                    $row->dirty[$key]=$value;
                }
                return $row->save();
            }
        }
        return (bool)$this->insert($data);
    }
    /////////////sql///////////////
    
    /**
     * @return Model
     */
    function where($w, ...$arr)
    {
        $this->sql->where($w, ...$arr);
        return $this;
    }
    /**
     * @return Model
     */
    function whereAnd($w, ...$arr)
    {
        $this->sql->and($w, ...$arr);
        return $this;
    }
    /**
     * @return Model
     */
    function whereOr($w, ...$arr)
    {
        $this->sql->or($w, ...$arr);
        return $this;
    }
    /**
     * @return Model
     */
    function limit($limit, $offset = 0)
    {
        $this->sql->limit($limit, $offset);
        return $this;
    }
    /**
     * @return Model
     */
    function order($order, ...$arr)
    {
        $this->sql->order($order, ...$arr);
        return $this;
    }
    /**
     * @return Model
     */
    function field($arr)
    {
        $this->sql->field($arr);
        return $this;
    }
    /**
     * @return Model
     */
    function join($str)
    {
        $this->sql->join($str);
        return $this;
    }
}
