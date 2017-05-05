<?php namespace dbm;


class Model implements \ArrayAccess ,\JsonSerializable
{  
    /** @var Connect  */
    public $db;
    /** @var Sql  */
    public $pq;
	public $data;
	public $dirty;
	
    public function __construct(Connect $db, $data=[],$pq=null)
    {
        $this->db=$db;
		$this->dirty= [];
        $this->data = $data; 
        if($pq instanceof Sql){
            $this->pq = $pq; 
        }else{
            $class = get_called_class();
            $this->pq= $db->sql($class);
            if($data){
                $pks = $class::$pks;
                foreach ($pks as $key ) {
                    $where[$key]=$data[$key]; 
                } 
                $this->pq->where($where); 
            } 
        }
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
        return isset($this->data[$offset]);
    }
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
    public function offsetSet($offset, $value)
    {
		if(empty($this->data[$offset]) || $this->data[$offset]!=$value)
            $this->dirty[$offset]=$value;
		$this->data[$offset]=$value;
    }
    public function offsetGet($offset)
    {
        if (isset($this->data[$offset])) {
            return $this->data[$offset];
        }
        if (class_exists($offset)) {
            $caller = get_called_class();
            if (isset($offset::$fks) && isset($offset::$fks[$caller])) {
                return $this->many($offset, $offset::$pks,$offset::$fks[$caller]);
            }
            if (isset($this->pq->pks)) {
                return $this->one($offset, $offset::$pks,$caller::$fks[$offset]);
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
            $sql->rArgs[$k]=$this->data[$this->pq->pks[$i]];
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
        //关联添加 $model->one()->insert();
        $sql->rModel=$this;
        $sql->rfks=$fks;
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
        $result = (clone $this->pq)->where($arr)->update($this->dirty);
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