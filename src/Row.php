<?php namespace dbm;


class Row implements \ArrayAccess ,\JsonSerializable
{  
    /** @var Connect  */
    public $db;
    /** @var Sql  */
    public $pq;
	public $data;
	public $dirty;
	
    public function __construct(Connect $db, $pq=null,$row=[])
    {
        $this->db=$db;
        $this->pq=$pq??$db->sql(get_called_class());
		$this->data=$row;
		$this->dirty=[];
    }
    public function __debugInfo()
    {
        return $this->data;
    }
    public function __toString()
    {
        return (string)$this->pq;
    }	
	function jsonSerialize() {
		return $this->data;
	}
    public function offsetExists($offset)
    {
    }
    public function offsetUnset($offset)
    {
    }
    public function offsetSet($offset, $value)
    {
		$this->dirty[$offset]=$value;
		$this->data[$offset]=$value;
    }
    public function offsetGet($offset)
    {
        if (isset($this->data[$offset])) {
            return $this->data[$offset];
        }
        if (class_exists($offset)) {
            if (isset(static::$fks)) {
                return $this->one($offset, $offset::$pks, static::$fks[$offset]);
            }
            if (isset($offset::$fks)) {
            	$caller = get_called_class();
                return $this->many($offset, static::$pks,$offset::$fks[$caller]);
            }
        } 
    }
 
    public function val($name)
    {
        return $this->data[$name];
    }
	public function toArray(){
		return $this->data;
	}
    public function many($table, $table_pks, $table_fks):Sql
    {
        $pks=(array)$table_pks;
        $fks=(array)$table_fks;

        $sql = $this->db->sql($table,...$pks);
        foreach ($fks as $i => $k) {
            $sql->rArgs[$k]=$this->data[$pks[$i]];
        }
        return $sql->and($this->kv($pks,$fks));
    }
    public function one($table, $table_pks, $self_fks):Sql
    {
        $pks=(array)$table_pks;
        $fks=(array)$self_fks;

		
        $sql = $this->db->sql($table,...$pks);
        foreach ($pks as $i => $k) {
            $sql->rArgs[$k]=$this->data[$fks[$i]];
        }
        return $sql->and($this->kv($fks, $pks));
    }

    function kv(array $self_ks, array $table_ks)
    { 
        foreach ($this->pq->getAll() as $obj) {
            foreach ($table_ks as $i => $k) {
                $arr[$k][] = $obj[$self_ks[$i]];
            }
        }
        foreach ($arr as &$unique) {
            $unique=array_unique($unique);
			sort($unique);
        }
        return $arr??[];
    }
 	function pkv($pks = null)
    { 
        foreach ($pks??$this->pq->pks as $i => $key) {
            if (!isset($this->data[$key])) {
                return false;
            }
            $arr[$key] = $this->data[$key];
        }
        return $arr;
    }

	
    public function create():bool
    {  
        $this->pq->insert($this->dirty);
        if ($last_id = $this->db->lastInsertId()) {
        	$pks = $this->pq->pks;
            if (count($pks)==1) {
                $this->data[$pks[0]] = $last_id;
            }
        }
        //???????????????????
        if ($arr = $this->pkv()) {
            $this->pq->where($arr);

        }
        $this->dirty=[];//clear
        return true;
    }
    public function save($pks=null):bool
    { 
        if (empty($this->dirty)) {
            return false;
        }
        if (!($arr = $this->pkv($pks))) {
			if(!$this->create())
				throw new Exception("Error Processing Request", 1);
			return $this->data;
        } 
        $result = $this->pq->where($arr)->update($this->dirty);
        $this->dirty=[];//clear
        return $result;
    }
    public function destroy($pks=null):bool
    { 
        if (!($arr = $this->pkv($pks))) {
            throw new \Exception("Error Processing Request", 1);
        } 
        $result = $this->pq->where($arr)->delete();
        return $result;
    }
}