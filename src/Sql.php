<?php namespace dbm;


class Sql implements \IteratorAggregate, \ArrayAccess
{ 
	use SqlIterator;
	use SqlAccess;
	use SqlRelation; 
	use SqlSetter;   
	use SqlGetter;
 
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

	/////////////////////////////////////////


 	public function limit($limit, $offset = 0):Sql
    {
        $this->lStr=" LIMIT ".intval($limit);
        if (!empty($offset)) {
            $this->lStr.=' OFFSET '.intval($offset).' ';
        }
        return $this;
    }
    public function order(string $order, ...$arr) :Sql
    {
        $this->oStr=" ORDER BY ".$order;
        $this->oArgs=$arr;
        return $this;
    }
    public function field($fields) :Sql
    {
        $this->fStr=$this->kvSQL($this->fArgs, ',', $fields );
        return $this;
    }

    public function where($w, ...$arr) :Sql
    {
		$this->wArgs=[];
        $this->wStr=' WHERE '.$this->kvSQL($this->wArgs , ' AND ', $w, $arr);
        return $this;
    }
    public function and($w, ...$arr) :Sql
    {
        $this->wStr.=empty($this->wStr)?" WHERE ":" AND ";
        $this->wStr.=$this->kvSQL($this->wArgs, ' AND ', $w, $arr);
        return $this;
    }
    public function or($w, ...$arr) :Sql
    {
        $this->wStr.=empty($this->wStr)?" WHERE ":" OR ";
        $this->wStr.=$this->kvSQL($this->wArgs, ' OR ', $w, $arr);
        return $this;
    }

    public function find(...$pkv):Sql
	{
		return $this(...$pkv); 
    } 


	//////////////////////////////////////////////


	public function each(Closure $fn):SQL{
		foreach ($this as $row) 
			$fn( new $this->model($this->db,$row,$this) ); 
		return $this;
	} 
	public function all($key=null):array#[]
    { 
        foreach($this as $row)
            $arr[] = $key?$row[$key]:$row;
        return $arr??[]; 
    }
    public function keypair($key,$val=null):array#[]
    { 
        foreach($this as $row)
            $arr[$row[$key]] = $val?$row[$val]:$row;
        return $arr??[]; 
    } 
	
    public function get($offset = 0):array
    {
        return $this[$offset];
    }  
	public function val($field) 
	{
		foreach($this->field($field)->getAll() as $row){
			return $row[$field];
		} 
	}



}
