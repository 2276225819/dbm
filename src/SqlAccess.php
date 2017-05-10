<?php namespace dbm;

trait SqlAccess
{ 
	public static $gc;
    public function __construct(Connect $db, $table, $pks, $model )
    {
        $this->db=$db;
        $this->model=$model;
        $this->table=$table;
        $this->pks=(array)$pks;
        // if($this->db->debug)
        //     echo str_repeat("\t",static::$gc).
        //         "<!--> $this->table($this->model) !!!-->\n";
		++static::$gc; 
    } 
	public function __destruct(){ 
		--static::$gc;
        // if($this->db->debug)
        //     echo str_repeat("\t",static::$gc).
        //         "<!--< $this->table($this->model) !!!-->\n"; 
		if(!static::$gc) { 
            // if($this->db->debug)
            //     echo "<!--GC-->\n";
			static::$qs=[];
			static::$cs=[]; 
		}
	}
    public function __clone(){
		++static::$gc;//new
    }  

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
		//offset > Row
		if(is_numeric($offset)){ 
			foreach ($this as $row) { 
				if ($offset--<=0) {
					break;
				}
			}
			return $row??null;
		}
		//relation > SQL
		if(class_exists($offset)){
			return $this->ref($offset,$offset::$pks,$this->model::$ref[$offset]); 
		}
        //first > mixed
		foreach($this as $row){
			return $row[$offset];
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
