<?php
class Row1 extends ArrayObject
{
    public function offsetGet($offset){
        return parent::offsetGet($offset);
    }
    public function offsetSet($offset,$value){
        parent::offsetSet($offset,$value);
    } 
}


class Row2 implements IteratorAggregate, ArrayAccess , Serializable ,JsonSerializable ,Countable
{
    public $data;
    public function getIterator (){
        return new ArrayIterator($this->data);
    }

    public function offsetGet($offset){
        return $this->data[$offset];
    }
    public function offsetSet($offset,$value){
        $this->data[$offset] = $value;
    }
    public function offsetExists($offset){
        throw new \Exception("Error Processing Request", 1); 
    }
    public function offsetUnset($offset){
        throw new \Exception("Error Processing Request", 1);
    }

    public function serialize (  ){
        return serialize($this->data);  
    }
    public function unserialize ( $serialized ){
        $this->data = unserialize($data); 
    }  

    public function jsonSerialize() {
        return $this->data;
    }

    public function count(){
        return count($this->data);
    }

    public function __toArray(){
        return $this->data;
    }
}

$t = microtime(true);
$b = new Row1;
for ($i=0; $i < 1000000; $i++) { 
    $b[$i] = $i;
}
for ($i=0; $i < 1000000; $i++) { 
    $b[1000000-$i] = $b[$i];
}
echo microtime(true)-$t;
echo "\n";



$t = microtime(true);
$a = new Row2;
for ($i=0; $i < 1000000; $i++) { 
    $a[$i] = $i;
}
for ($i=0; $i < 1000000; $i++) { 
    $a[1000000-$i] = $a[$i];
}
echo microtime(true)-$t;
echo "\n";

$a= new Row1;
$a['a']='1';
$a->b='2';
$b= new Row2;
$b['a']='1';
$b->b='2';
echo "\n";
echo json_encode([$a,$b],JSON_PRETTY_PRINT);
echo "\n";
print_r([(array)$a,(array)$b]);
echo "\n";
print_r([$a,$b]);
