<?php namespace dbm;

class Sql implements \IteratorAggregate, \ArrayAccess
{

 
    use SqlIterator;
    use SqlAccess;
    use SqlRelation;
    use SqlGetter;

    ///////////////  value  /////////////////////
 
    /**
     * $sql->val() as Model | $sql->val(FILED) as Model
     * @param int $offset
     * @return Model
     */
    public function get($offset=NULL)
    { 
        return $this[$offset];
    }
    /**
     * $sql->val(FILED) as mixed
     * @param string $field
     * @return mixed
     */
    public function val($field=NULL)
    {  
        foreach ($this->getAllIterator() as $row) {
            foreach ($this->rArgs as $k => $v) 
                if ($row[$k]!=$v) 
                    continue 2;
            return $field?$row[$field]:$row;
        }
    } 
    /**
     * Row
     * @param array ...$pkv
     * @return Model
     */
    public function load(...$pkv) 
    {
        return $this(...$pkv);
    }


    /**
     * void
     * @param Closure $fn
     * @return void
     */
    public function each($fn)
    {
        foreach ($this as $row) {
            $fn( $row );
        } 
    }
    /**
     * array
     * @param Closure $fn
     * @return array
     */
    public function map($fn) 
    {
        $result=[];
        foreach ($this as $row) {
            $result[] = $fn( $row );
        }
        return $result;
    } 
    /**
     * [ $Row, $Row... ] | [ $key, $key... ]
     * @param string $key
     * @return Model[]
     */
    public function all($key = null) 
    {
        $result=[];
        foreach ($this as $row) {
            $result[] = $key?$row[$key]:$row;
        }
        return $result;
    }
    /**
     * [ $key=>Row, $key=>Row... ] | [ $key => $val, $key => $val... ]
     * @param string $key
     * @param string $val
     * @return Model[]
     */
    public function keypair($key, $val = null) 
    {
        $result=[];
        foreach ($this as $row) {
            $result[$row[$key]] = $val?$row[$val]:$row;
        }
        return $result;
    }
    
 
    //////////////  select  ///////////////////////////

    /**
     * Undocumented function
     *
     * @param string $model
     * @param array $pks
     * @param array $ref
     * @return Sql
     */
	public function ref($model,$pks=NULL,$ref=NULL){
        $CLASS = $this->model;
		if(is_string($pks))$pks = (array)$pks;
		if(!is_array($pks))$pks = $CLASS::$pks;
		if(!is_array($ref))$ref = $CLASS::$ref[$model];
		return $this->relation($model,(array)$pks,(array)$ref);
	}

    /**
     * ... LIMIT {$limit} OFFSET {$offset} ...
     * @param int $limit
     * @param int $offset
     * @return Sql
     */
    public function limit($limit, $offset = 0) 
    {
        $this->lStr=" LIMIT ".intval($limit);
        if (!empty($offset)) {
            $this->lStr.=' OFFSET '.intval($offset).' ';
        }
        return $this;
    }
    /**
     * ... ORDER {$order} ...
     * @param string $order
     * @param array ...$arr
     * @return Sql
     */
    public function order(string $order, ...$arr)  
    {
        $this->oStr=" ORDER BY ".$order;
        $this->oArgs=$arr;
        return $this;
    }
    /**
     * SELECT {$fileds} FROM ...
     * @param string|array $fields
     * @return Sql
     */
    public function field($fields)  
    {
        $this->fStr=$this->kvSQL($this->fArgs, ',', $fields);
        return $this;
    }

    /**
     * ... WHERE {$w} ...
     * @param string|array $w
     * @param array ...$arr
     * @return Sql
     */
    public function where($w, ...$arr)  
    {
        $this->wArgs=[];
        $this->wStr=' WHERE '.$this->kvSQL($this->wArgs, ' AND ', $w, $arr);
        return $this;
    }
    /**
     * ... WHERE ... AND {$w} ...
     * @param string|array $w
     * @param array ...$arr
     * @return Sql
     */
    public function and($w, ...$arr)  
    {
        $this->wStr.=empty($this->wStr)?" WHERE ":" AND ";
        $this->wStr.=$this->kvSQL($this->wArgs, ' AND ', $w, $arr);
        return $this;
    }
    /**
     * ... WHERE ... OR {$w} ...
     * @param string|array $w
     * @param array ...$arr
     * @return Sql
     */
    public function or($w, ...$arr)  
    {
        $this->wStr.=empty($this->wStr)?" WHERE ":" OR ";
        $this->wStr.=$this->kvSQL($this->wArgs, ' OR ', $w, $arr);
        return $this;
    }

    /**
     * ... WHERE `PrimaryKey` = {$pkv} ...
     * @param string|array $w
     * @param array ...$arr
     * @return Sql
     */
    public function find(...$pkv) 
    {
        if (is_array($pkv[0] && empty($pkv[0][0]))) {
            $arr = $pkv[0];
        } else {
            $arr = array_combine($this->pks, $pkv);
        }
        return $this->and($arr);
    }
    
    ///////////////  update  ///////////////////
    

    /**
     * Row
     * @param array $data
     * @param int $auto_increment_key
     * @return Model
     */
    public function insert($data, $auto_increment_key = null)
    {
        $data = array_merge($data,$this->rArgs,$this->sArgs);
        $sql="INSERT INTO {$this->table} SET ".$this->kvSQL($param, ',', $data);
        if (!($query = $this->db->execute($sql, $param))) {
            throw new \Exception("Error Processing Insert" );
        }
        //AUTO INCREMENT
        $last_id = $this->db->lastInsertId();
        if (!empty($last_id)) {
            $key = $auto_increment_key?$auto_increment_key:$this->pks[0];
            $data[$key]=$last_id;
            if (isset($this->rModel)) { 
                foreach ($this->rref as $i => $k) {
                    $this->rModel[$k]=$data[$i];
                }
                $this->rModel->save();
            }
        }
        $row = new $this->model($this->db, $data, $this);
        return $row;
    }
    /**
     * RowCount
     * @param array $list
     * @return int
     */
    public function insertMulit($list)  
    {
        $param=[];
        $sql1 = "";
        $sql2 = "";
        foreach ($list as &$arr) {
            $arr = array_merge($arr, $this->sArgs, $this->rArgs);
            $sql2.=",(".substr(str_repeat(",?", count($arr)), 1).")";
            foreach ($arr as $value) 
                $param[]=$value; 
        }
        foreach ($list[0] as $key => $value) {
            $sql1.=",`{$key}`";
        }
        $sql="INSERT INTO {$this->table} (".substr($sql1, 1)." )VALUES".substr($sql2, 1);
        if (!($query = $this->db->execute($sql, $param))) {
            throw new \Exception("Error Processing Insert Mulit", 1);
        }
        return $query->rowCount();
    }
    /**
     * RowCount
     * @param array $data
     * @param array ...$arr
     * @return int
     */
    public function update($data, ...$arr)  
    {
        if (empty($this->wStr)) {
            throw new \Exception("Require Where Column", 1);
        }
        $param=[];
        $data=$this->kvSQL($param, ',', $data, $arr);
        $sql="UPDATE {$this->table} SET {$data} {$this->wStr}";
        $param = array_merge($param, $this->wArgs);
        if (!($query = $this->db->execute($sql, $param))) {
            throw new \Exception("Error Processing Update", 1);
        }
        return $query->rowCount();
    }
    /**
     * RowCount
     * @return int
     */
    public function delete($force=false)  
    {
        if (!$force && empty($this->wStr)) {
            return false;
        }
        $sql="DELETE FROM {$this->table} {$this->wStr}";
        if (!($query = $this->db->execute($sql, $this->wArgs))) {
            throw new \Exception("Error Processing Delete", 1);
        }
        return $query->rowCount();
    }
    /**
     * insert or update
     * @param array $data
     * @return void
     */
    public function set($data)
    {
        $data = array_merge($this->rArgs, $data);
        foreach ($this->pks as $key) {
            if (isset($data[$key]) && in_array($key, $this->pks)) {
                $where[$key]=$data[$key];
            }
        }
        if (empty($where)) {
            $this->insert($data);
        } elseif ($row = $this->where($where)->get()) {
            foreach ($data as $key => $value) {
                $row[$key]=$value;
            }
            $row->save();
        } else {
            throw new Exception("Error Processing Request", 1);
        }
    }
}
