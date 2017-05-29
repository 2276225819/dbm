<?php namespace dbm;

class Pql
{
    public $table='',$jStr='', $wStr='',$lStr='',$oStr='',$fStr='*';
    public $rArgs=[],$wArgs=[], $fArgs=[], $sArgs=[],$oArgs=[];
	public $pks=[]; 
    
    public function __construct($table,$pks)
    {
        $this->table=$table;
        $this->pks=(array)$pks;
    } 

    public function __toString()
    {
        if(empty($this->hash)){
            $this->hash = Connect::bulidSql($this->bulidSelect()).';'.join($this->bulidArgs(), ',');
        }
        return $this->hash;
    }
	public function uncache(){
        $this->hash=null;
        return $this;
    }
    /**
     * ... LIMIT {$limit} OFFSET {$offset} ...
     * @param int $limit
     * @param int $offset
     * @return Pql
     */
    public function limit($limit, $offset = 0)
    {
        $this->lStr=" LIMIT ".intval($limit);
        if (!empty($offset)) {
            $this->lStr.=' OFFSET '.intval($offset).' ';
        }
        return $this->uncache();
    }
    /**
     * ... ORDER {$order} ...
     * @param string $order
     * @param array ...$arr
     * @return Pql
     */
    public function order(string $order, ...$arr)
    {
        $this->oStr=" ORDER BY ".$order;
        $this->oArgs=$arr;
        return $this->uncache();
    }
    /**
     * SELECT {$fileds} FROM ...
     * @param string|array $fields
     * @return Pql
     */
    public function field($fields)
    {
		if(is_array($fields)){
			$this->fStr=join($fields,',');
		}
		else{
			$this->fStr=$fields;
		} 
        return $this->uncache();
    }

    /**
     * ... WHERE {$w} ...
     * @param string|array $w
     * @param array ...$arr
     * @return Pql
     */
    public function where($w, ...$arr)
    {
        if(!empty($w)){
            $this->wArgs=[];
            $this->wStr=" WHERE (".$this->kvSQL($this->wArgs, ' AND ', $w, $arr).")";
        }
        return $this->uncache();
    }
    /**
     * ... WHERE ... AND {$w} ...
     * @param string|array $w
     * @param array ...$arr
     * @return Pql
     */
    public function and($w, ...$arr)
    {
        if(!empty($w)){
            $this->wStr.=empty($this->wStr)?" WHERE ":" AND ";
            $this->wStr.="(".$this->kvSQL($this->wArgs, ' AND ', $w, $arr).")"; 
        }
        return $this->uncache();
    }
    /**
     * ... WHERE ... OR {$w} ...
     * @param string|array $w
     * @param array ...$arr
     * @return Pql
     */
    public function or($w, ...$arr)
    {
        if(!empty($w)){
            $this->wStr.=empty($this->wStr)?" WHERE ":" OR ";
            $this->wStr.="(".$this->kvSQL($this->wArgs, ' OR ', $w, $arr).")";
        }
        return $this->uncache();
    }
    public function find(...$pkv){ 
        if (is_array($pkv[0] && empty($pkv[0][0]))) {
            $arr = $pkv[0];
        } else {
            $arr = array_combine($this->pks, $pkv);
        }
        $this->rArgs=$arr;
        return $this->where($arr)->uncache();
    }
    
    /**
     * ... FROM [TABLE] JOIN {$str} ...
     * @param string  $str
     * @return Pql
     */
    public function join($str)
    {
        $this->jStr=" $str";
        return $this->uncache();
    }
    public function kvSQL(&$param, $jtag = ' AND ', $arr, $attr = null, $sql = '')
    {
        if (is_array($arr)) {
            foreach ($arr as $key => $v) {
				if(strstr($key,',')){
					$key="($key)";
				}
                if($v instanceof Model){
                    $v = $v->field($this->pks)->sql;
                }
				if ($v instanceof Pql){ 
					$sql.="{$jtag}{$key} in (".$v->bulidSelect().")";
                	$param=array_merge($param, $v->bulidArgs());
					continue;
				}
                if (is_array($v)) {
                    if (count($v)>1) {
                        $str= substr(str_repeat(",?", count($v)), 1);
                        $sql.="{$jtag}{$key} in ($str) " ;
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
	public function bulidArgs()
    {
        // $arr = [];
        // foreach ($this->wArgs as $key => $value) { 
        //     $arr[] = $value;
        // }
        // foreach ($this->oArgs as $key => $value) { 
        //     $arr[] = $value;
        // }
        return array_merge($this->wArgs,$this->oArgs);
    }
    public function bulidSelect()
    {
        return "SELECT {$this->fStr} FROM {$this->table}{$this->jStr} {$this->wStr} {$this->oStr} {$this->lStr}";
    } 




}