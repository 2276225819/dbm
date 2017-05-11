<?php namespace dbm;


class Model implements \ArrayAccess ,\JsonSerializable
{   
    use ModelAccess;
    static $table;
    static $ref;
	
    /** @var Connect  */
    public $db;
	
    /** @var Sql  */
    public $pq;

    public function val($field)
    {
        return $this->data[$field];
    }
	public function toArray():array{
		return $this->data;
	}
    public function ref($model,$pks=NULL,$ref=NULL):Sql
    {
		if(is_string($pks))$pks=(array)$pks;
		if(!is_array($pks))$pks = static::$pks;
		if(!is_array($ref))$ref = static::$ref[$model];
 
		$sql= $this->pq->relation($model,(array)$pks,(array)$ref);
        foreach ($ref as $k => $v) {
            $sql->rArgs[$k]=$this[$v];
        }   
		$sql->rModel=$this;
		$sql->rref=$ref;
        return $sql;
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