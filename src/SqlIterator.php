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
    public function getAllIterator($i = 0)
    {
        $hash = $this->bulidHash();
        if (empty(static::$qs[$hash])) {
            $query=$this->db->execute($this->bulidSelect(), $this->bulidArgs());
			$query->setFetchMode(\PDO::FETCH_ASSOC);
            static::$qs[$hash]=$query;
            static::$cs[$hash]=[];
        }
        while (true) {
            if (static::$qs[$hash]===true) {
                for ($c=count(static::$cs[$hash]); $i < $c; $i++) 
                    yield static::$cs[$hash][$i]; 
                return;
            }
            if (isset(static::$cs[$hash][$i])) { 
                yield static::$cs[$hash][$i++];
                continue;
            }
            if ($i<2) {
                if ($row = static::$qs[$hash]->fetch()) { 
                    yield static::$cs[$hash][$i++]=$row;
                    continue;
                }
            }
            foreach (static::$qs[$hash]->fetchAll() as $value) { 
                yield static::$cs[$hash][]=$value;
            }
            static::$qs[$hash]=true; 
            return;
        }
    } 
    public function getIterator($i=0){ 
        if(!empty($this->rArgs)){
            foreach ($this->getAllIterator() as $row) {
                foreach ($this->rArgs as $k => $v) 
                    if ($row[$k]!=$v)  
                        continue 2; 
                yield new $this->model($this->db,$row,$this);
            }; 
        }else{
            foreach ($this->getAllIterator() as $row) {
                yield new $this->model($this->db,$row,$this);
            };  
        } 
    } 
}
