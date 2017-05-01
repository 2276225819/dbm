<?php namespace dbm;


class Query implements \IteratorAggregate, \ArrayAccess
{ 
    public $wStr='',$lStr='',$oStr='',$fStr='*';
    public $rArgs=[],$wArgs=[], $fArgs=[], $sArgs=[],$oArgs=[];
	
    public $model;
    public $table;
    public $pks;
	
    public  function __toString()
    {
        return $this->bulidHash();
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
        foreach ($this as $row) { 
            if ($offset--<=0) {
                break;
            }
        }
        return $row??null;
    }
	public function __invoke(...$pkv){
		if(is_array($pkv[0] && empty($pkv[0][0]))){
			$arr=$pkv[0];
		}else{
        	$arr = array_combine($this->pks, $pkv); 
		}
        return $this->and($arr)[0];
	}
	
  
    public function bulidHash()
    {
        return $this->bulidSelect().';'.join($this->bulidArgs(), ',');
    }
    public function bulidArgs()
    {
        return array_merge($this->wArgs, $this->oArgs);
    }
    public function bulidSelect()
    {
        return "SELECT {$this->fStr} FROM {$this->table} {$this->wStr} {$this->oStr} {$this->lStr}";
    }
	
    /** @var Connect */
    public $db;
    public function __construct(Connect $db, $table, $pks, $model )
    {
        $this->db=$db;
        $this->model=$model;
        $this->table=$table;
        $this->pks=(array)$pks;
		++static::$c;
    }

	public function __destruct(){ 
		--static::$c;
		if(!static::$c) { 
			static::$qs=[];
			static::$cs=[]; 
		}
	}

	
	static $c=0;
    /** @var PDOStatement[]  */
    static $qs=[];
    /** @var Model[]  */
    static $cs=[];
    public function getIterator($i = 0)
    {
        $hash = $this->bulidHash();
        if (empty(static::$qs[$hash])) {
            $query=$this->db->execute($this->bulidSelect(), $this->bulidArgs());
			$query->setFetchMode(\PDO::FETCH_ASSOC);
            static::$qs[$hash]=$query;
            static::$cs[$hash]=[];
        }
        $valid=function ($row) {
            foreach ($this->rArgs as $k => $v) {
                if ($row[$k]!=$v) {
                    return true;
                }
            }
            return false;
        };
		$conv=function($row){ 
			$mod = new $this->model($this->db,$this,$row); 
			return $mod;
		};
        while (true) {
            if (static::$qs[$hash]===true) {
                for ($c=count(static::$cs[$hash]); $i < $c; $i++) {
                    if ($valid(static::$cs[$hash][$i])) {
                        continue;
                    }
                    yield $conv(static::$cs[$hash][$i]);
                }
                return;
            }
            if (isset(static::$cs[$hash][$i])) {
                $row = static::$cs[$hash][$i++];
                if ($valid($row)) {
                    continue;
                }
                yield $conv($row);
                continue;
            }
            if ($i<2) {
                if ($row = static::$qs[$hash]->fetch()) {
                    static::$cs[$hash][$i++]=$row;
                    if ($valid($row)) {
                        continue;
                    }
                    yield $conv($row);
                    continue;
                }
            }
            foreach (static::$qs[$hash]->fetchAll() as $value) {
                static::$cs[$hash][]=$value;
                if ($valid($value)) {
                    continue;
                }
                yield $conv($value);
            }
            static::$qs[$hash]=true; 
            return;
        }
    }
	
    public function getAll(){
        $hash = $this->bulidHash();
        if (empty(static::$qs[$hash])) {
            $query=$this->db->execute($this->bulidSelect(), $this->bulidArgs());
			$query->setFetchMode(\PDO::FETCH_ASSOC);
            static::$qs[$hash]=$query;
            static::$cs[$hash]=[];
        } 
		if (static::$qs[$hash]!==true) {
			$arr =static::$qs[$hash]->fetchAll();
			static::$cs[$hash]=array_merge(static::$cs[$hash],$arr); 
		}
		foreach (static::$cs[$hash] as $row) {
			$result[]=new $this->model($this->db,$this,$row);
			# code...
		} 
		return $result;
	}
}

class Sql extends Query
{
    ///////////////////////////////////////    
	public function all($key=null)
    { 
        foreach($this as $row){
            $arr[] = $key?$row[$key]:$row;
        }
        return $arr??[]; 
    }
    public function keypair($key,$val=null)
    { 
        foreach($this as $row){
            $arr[$row[$key]] = $val?$row[$val]:$row;
        }
        return $arr??[]; 
    }
    public function load(...$pkv):Model
    {
		return $this(...$pkv);
    }
    public function get($offset = 0):Model
    {
        return $this[$offset];
    }  
	public function val($field){
		foreach($this->field($field)->getAll() as $row){
			return $row[$field];
		}

	}

	//////////////////////////////////
	public function insert($data, $auto_increment_key = null)
    {
        $data = array_merge($data,$this->rArgs, $this->sArgs);
        $sql="INSERT INTO {$this->table} SET ".$this->kvSQL($param, ',', $data);
        if (!($query = $this->db->execute($sql, $param))) {
            throw new \Exception("Error Processing Insert" );
        }
        //AUTO INCREMENT
        $last_id = $this->db->lastInsertId();
        if(!empty($last_id)){
            $data[$auto_increment_key??$this->pks[0]]=$last_id; 
        } 
        $row = new $this->model($this->db,$this,$data); 
        return $row;
    }
  	public function insertMulit($list) :int
    {
        $param=[];
        $sql1 = "";
        $sql2 = "";
        foreach ($list as &$arr) {
            $arr = array_merge($arr, $this->sArgs,$this->rArgs);
            $sql2.=",(".substr(str_repeat(",?", count($arr)), 1).")";
            array_push($param, ...array_values($arr));
        }
        foreach ($list[0] as $key => $value) {
            $sql1.=",`{$key}`";
        }
        $sql="INSERT INTO {$this->table} (".substr($sql1, 1)." )VALUES".substr($sql2, 1);
        if (!($query = $this->db->execute($sql, $param))) {
            throw new \Exception("Error Processing Insert Mulit", 1);
        }
        return $query->rowCount();
    }
    public function update($data, ...$arr) :int
    {
        if (empty($this->wStr)) {
            throw new \Exception("Require Where Column", 1);
        }
        $param=[];
        $data=$this->kvSQL($param, ',', $data, $arr);
        $sql="UPDATE {$this->table} SET {$data} {$this->wStr}";
        $param = array_merge($param, $this->wArgs);
        if (!($query = $this->db->execute($sql, $param))) {
            throw new \Exception("Error Processing Update", 1);
        }
        return $query->rowCount();
    }
    public function delete() :int
    {
        if (empty($this->wStr)) {
            return false;
        }
        $sql="DELETE FROM {$this->table} {$this->wStr}";
        if (!($query = $this->db->execute($sql, $this->wArgs))) {
            throw new \Exception("Error Processing Delete", 1);
        }
        return $query->rowCount();
    }










	///////////////////////////////////////
	

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

    private function kvSQL(&$param, $jtag = ' AND ', $arr, $attr = null, $sql = ''):string
    {
        if (is_array($arr)) {
            foreach ($arr as $key => $v) {
                if (is_array($v)) {
                    if (count($v)>1) {
                        $str= substr(str_repeat(",?", count($v)), 1);
                        $sql.="{$jtag} {$key} in ($str) " ;
                        $param=array_merge($param, $v);
                        continue;
                    } else {
                        $v=$v[0];
                    }
                }
                $sql.= "{$jtag}{$key}=?";
                $param[]=$v;
            }
            $sql=substr($sql, strlen($jtag));
        } else {
            $sql=$arr;
            if (is_array($attr)) {
                $param=array_merge($param, $attr);
            }
        }
        return $sql;
    }
}
