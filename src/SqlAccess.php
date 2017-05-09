<?php namespace dbm;

trait SqlAccess
{ 
    public function offsetUnset($offset)
    {
    }
    public function offsetSet($offset, $value)
    {
    }
    public function offsetExists($offset)
    {
        return $this[$offset];
    }
    public function offsetGet($offset)
    {
		//offset
		if(is_numeric($offset)){ 
			foreach ($this as $row) { 
				if ($offset--<=0) {
					break;
				}
			}
			return $row??null;
		}
		//relation
		if(class_exists($offset)){
			return $this->ref($offset,$this->model::$ref[$offset]); 
		}
 
    }
	public function __invoke(...$pkv){
		if(is_array($pkv[0] && empty($pkv[0][0]))){
			$arr=$pkv[0];
		}else{
        	$arr = array_combine($this->pks, $pkv); 
		}
        return $this->and($arr);
	}
}
