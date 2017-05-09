<?php namespace dbm;

trait SqlIterator
{   
    public function __toString()
    {
        return $this->bulidHash();
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
        while (true) {
            if (static::$qs[$hash]===true) {
                for ($c=count(static::$cs[$hash]); $i < $c; $i++) {
                    if ($valid(static::$cs[$hash][$i])) {
                        continue;
                    }
                    yield static::$cs[$hash][$i];
                }
                return;
            }
            if (isset(static::$cs[$hash][$i])) {
                $row = static::$cs[$hash][$i++];
                if ($valid($row)) {
                    continue;
                }
                yield $row;
                continue;
            }
            if ($i<2) {
                if ($row = static::$qs[$hash]->fetch()) {
                    static::$cs[$hash][$i++]=$row;
                    if ($valid($row)) {
                        continue;
                    }
                    yield $row;
                    continue;
                }
            }
            foreach (static::$qs[$hash]->fetchAll() as $value) {
                static::$cs[$hash][]=$value;
                if ($valid($value)) {
                    continue;
                }
                yield $value;
            }
            static::$qs[$hash]=true; 
            return;
        }
    }

    public function getAll(){
        $hash = $this->bulidHash();
        if (empty(static::$qs[$hash])) {
            $query=$this->db->execute($s=$this->bulidSelect(), $a=$this->bulidArgs());
			$query->setFetchMode(\PDO::FETCH_ASSOC);
            static::$qs[$hash]=$query;
            static::$cs[$hash]=[];
        } 
		if (static::$qs[$hash]!==true) {
			$arr =static::$qs[$hash]->fetchAll();
			static::$cs[$hash]=array_merge(static::$cs[$hash],$arr); 
		}
		foreach (static::$cs[$hash] as $row) {
            $model = new $this->model($this->db,$row,$this ); 
			$result[]=$model; 
		} 
		return $result;
	}


	public function map(Closure $fn):array{
		foreach ($this as $row) 
			$result[] = $fn( new $this->model($this->db,$row,$this) );
		return $result;
	}
}
