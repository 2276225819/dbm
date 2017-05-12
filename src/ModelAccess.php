<?php namespace dbm;

trait ModelAccess
{ 	 

	public $data;
	public $dirty; 
    public function __construct(Connect $db,$data=[],$pq=null)
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

	public function jsonSerialize() {
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
            return $this->ref($offset,$offset::$pks,static::$ref[$offset]); ;
        } 
    }
    public function __debugInfo()
    {
        return (array)$this->data;
    }
    public function __toString()
    {
        return (string)$this->pq;
    }
    

    // function kv(array $self_ks, array $table_ks)
    // { 
    //     foreach ($this->pq->getAll() as $obj) {
    //         foreach ($table_ks as $i => $k) {
    //             $arr[$k][] = $obj[$self_ks[$i]];
    //         }
    //     }
    //     foreach ($arr as &$unique) {
    //         $unique=array_unique($unique);
	// 		sort($unique);
    //     }
    //     return $arr??[];
    // }
 	function pkv($pks = null)
    { 
        if(empty($pks)) $pks = $this->pq->pks;
        foreach ($pks as $i => $key) {
            if (!isset($this->data[$key])) {
                return false;
            }
            $arr[$key] = $this->data[$key];
        }
        return $arr;
    }

}

