<?php namespace dbm;


class Entity implements \ArrayAccess ,\JsonSerializable
{   
    use EntityAccess;
    static $table;
    static $ref;
	
    /** @var Connect  */
    public $db;
	
    /** @var Sql  */
    public $pq;

    /** 
     * @param string $field
     * @return mixed
     */
    public function val($field)
    {
        return $this->data[$field];
    }
    /** 
     * @return array
     */
	public function toArray() {
		return $this->data;
	}
    /** 
     * @param string $model
     * @param array $pks
     * @param array $ref
     * @return Sql
     */
    public function ref($model,$pks=NULL,$ref=NULL) 
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
    /** 
     * @return bool
     */
    public function create() 
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

    /** 
     * @param array $pks
     * @return bool
     */
    public function save($pks=null)
    { 
        if (empty($this->dirty)) {
            return false;
        }
        if (!($arr = $this->pkv($pks))) {
			if(!$this->create())
				throw new Exception("Error Processing Request", 1);
			return $this->data;
        } 
        $sql = clone $this->pq;
        $result = $sql->where($arr)->update($this->dirty);
        $this->dirty=[];//clear
        return $result;
    }
    /** 
     * @param array $pks
     * @return bool
     */
    public function destroy($pks=null)
    { 
        if (!($arr = $this->pkv($pks))) {
            throw new \Exception("Error Processing Request", 1);
        } 
        $result = $this->pq->where($arr)->delete();
        return $result;
    }
}