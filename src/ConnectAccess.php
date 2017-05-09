<?php namespace dbm;

trait ConnectAccess{

    public function offsetUnset($offset)
    {
    }
    public function offsetSet($offset, $value)
    {
    }
    public function offsetExists($offset)
    {  
    }  
    public function offsetGet($offset)
    {
		return $this->sql($offset);
    } 

}