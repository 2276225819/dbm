<?php namespace dbm;

class Model implements \IteratorAggregate, \ArrayAccess, \JsonSerializable
{

    use ModelAccess;
    public static $table='...';
    public static $pks=[];
    public static $ref=[];
    public static function new($conn)
    {
        $sql = new Sql;
        $sql->table = static::$table;
        $sql->pks = static::$pks;
        return new static($sql, new Session($conn));
    }
    /////////////model///////////////
    function get($offset = null)
    {
        if (is_numeric($offset)) {
            $this->limit($offset, 1);
            $this->list=null;
            $offset=0;
        }
        if (empty($this->list)) {
            $this->list = $this->session->select($this->sql);
        }
        if (empty($this->list[$offset])) {
            return null;
        }
        $model = new static($this->sql, $this->session);
        $model->list = [$this->list[$offset]];
        foreach ($this->sql->pks as $key) {
            $model->sql->rArgs[$key]=$this->list[$offset][$key];
        }
        return $model;
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
    function load(...$pkv)
    {
        $this->find(...$pkv);
        if (!isset($this->list)) {
            $this->list = $this->session->select($this->sql);
        }
        foreach ($this->sql->pks as $key) {
            $this->sql->rArgs[$key]=current($pkv);
            next($pkv);
        }
        return $this;
    }
    function all($field = null)
    {
        if (empty($field)) {
            return iterator_to_array($this);
        }
        if (!isset($this->list)) {
            $this->list = $this->session->select($this->sql);
        }
        foreach ($this->list as $row) {
            $arr[]=$row[$field];
        }
        return $arr??[] ;
    }
    function keypair($key, $val = null)
    {
        $arr=[];
        if (empty($this->list)) {
            $this->list = $this->session->select($this->sql);
        }
        foreach ($this->list as $row) {
            $arr[$row[$key]] = $val?$row[$val]:$row;
        }
        return $arr;
    }
    
    // public $dirty=[];
    // public $list=[];
    function val($field, $val = null)
    {
        if (isset($val)) {
            return $this->dirty[$field]=$val;
        } else {
            if (!isset($this->list)) {
                $this->list = $this->session->select($this->sql);
            }
            return $this->list[0][$field];
        }
    }
    function save($dirty = null)
    {
        if (isset($dirty)) {
            $this->dirty=$dirty;
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

    
    function ref($model, $pks = null, $ref = null)
    {
        $sql=new Sql();
        if (class_exists($model)) {
            $sql->table = $model::$table;
            if (!is_array($pks)) {
                $pks = static::$pks;
            }
            if (!is_array($ref)) {
                $ref = static::$ref[$model];
            }
        } else {
            $sql->table = $model;
            $model = static::class;
        }
        $sql->pks = (array)$pks;

        $model = new $model($sql, $this->session);
        if (!isset($this->list)) {
            $keys=join(array_keys($ref), ',');
            $query = $this->sql->field(array_values($ref));
            $sql->and([$keys=>$query]);
            $_ref = array_flip($ref);
            foreach ($this->sql->rArgs as $k => $v) {
                if (isset($_ref[$k])) {
                    $sql->rArgs[$_ref[$k]]=$v;
                }
            }
        } else {
            $thisdata = $this->session->select($this->sql, true);
            foreach ($ref as $k => $f) {
                foreach ($thisdata as $row) {
                    $s[$k][]=$row[$f];
                }
                $s[$k] = array_unique($s[$k]);
            }
            $sql->and($s);
            foreach ($ref as $k => $v) {
                $sql->rArgs[$k]=$this->val($v);
            }
        }

        //$sql->rsql=$this->sql;
        //$sql->rref=$this->ref;

        return $model;
    }
    /////////////curd/////////////
    function insert($arr)
    {
        $arr = $this->session->insert($this->sql, $arr);
        // if (isset($this->rModel)) {
        // 	foreach ($this->rref as $i => $k) {
        // 		$this->rModel[$k]=$data[$i];
        // 	}
        // 	$this->rModel->save();
        // }
        $row = new self($this->sql,$this->session);
        $pk = $this->sql->pks[0];
        $row->where([$pk=>$arr[$pk]]);
        $row->list = [$arr];
        return $row;
    }

    function insertMulit($list)
    {
        if (!count($list)) {
            throw new \Exception("Error Muilt Column", 1);
        }
        $count = $this->session->insertMulit($this->sql, $list);
        return $count;
    }

    function update($arr)
    {
        if (empty($this->sql->wStr)) {
            throw new \Exception("Require Where Column", 1);
        }
        $count = $this->session->update($this->sql, $arr);
        return $count;
    }

    function delete($force = false)
    {
        if (!$force && empty($this->sql->wStr)) {
            throw new \Exception("Require Where Column", 1);
        }
        $count = $this->session->delete($this->sql);
        return $count;
    }

    /////////////sql///////////////
    function where($w, ...$arr)
    {
        $this->sql->where($w, ...$arr);
        return $this;
    }
    function and($w, ...$arr)
    {
        $this->sql->and($w, ...$arr);
        return $this;
    }
    function or($w, ...$arr)
    {
        $this->sql->or($w, ...$arr);
        return $this;
    }
    function limit($limit, $offset = 0)
    {
        $this->sql->limit($limit, $offset);
        return $this;
    }
    function order($order, ...$arr)
    {
        $this->sql->order($order, ...$arr);
        return $this;
    }
    function field($arr)
    {
        $this->sql->field($arr);
        return $this;
    }
    function join($str)
    {
        $this->sql->join($str);
        return $this;
    }
}
