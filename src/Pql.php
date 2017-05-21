<?php namespace dbm;

class Pql
{
    public $table='',$jStr='', $wStr='',$lStr='',$oStr='',$fStr='*';
    public $rArgs=[],$wArgs=[], $fArgs=[], $sArgs=[],$oArgs=[];
	public $pks; 
    public function __toString()
    {
        return $this->bulidSelect().';'.join($this->bulidArgs(), ',');
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
        return $this;
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
        return $this;
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
        return $this;
    }

    /**
     * ... WHERE {$w} ...
     * @param string|array $w
     * @param array ...$arr
     * @return Pql
     */
    public function where($w, ...$arr)
    {
        $this->wArgs=[];
        $this->wStr=' WHERE '.$this->kvSQL($this->wArgs, ' AND ', $w, $arr);
        return $this;
    }
    /**
     * ... WHERE ... AND {$w} ...
     * @param string|array $w
     * @param array ...$arr
     * @return Pql
     */
    public function and($w, ...$arr)
    {
        $this->wStr.=empty($this->wStr)?" WHERE ":" AND ";
        $this->wStr.=$this->kvSQL($this->wArgs, ' AND ', $w, $arr);
        return $this;
    }
    /**
     * ... WHERE ... OR {$w} ...
     * @param string|array $w
     * @param array ...$arr
     * @return Pql
     */
    public function or($w, ...$arr)
    {
        $this->wStr.=empty($this->wStr)?" WHERE ":" OR ";
        $this->wStr.=$this->kvSQL($this->wArgs, ' OR ', $w, $arr);
        return $this;
    }
    
    /**
     * ... FROM [TABLE] JOIN {$str} ...
     * @param string  $str
     * @return Pql
     */
    public function join($str)
    {
        $this->jStr=" $str";
        return $this;
    }
    public function from($table)
    {
        $this->table=$table;
    } 
    public function kvSQL(&$param, $jtag = ' AND ', $arr, $attr = null, $sql = '')
    {
        if (is_array($arr)) {
            foreach ($arr as $key => $v) {
				if(strstr($key,',')){
					$key="($key)";
				}
				if ($v instanceof Sql){ 
					$sql.="{$jtag} {$key} in (".$v->bulidSelect().")";
                	$param=array_merge($param, $v->bulidArgs());
					continue;
				}
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
	public function bulidArgs()
    {
        return array_merge($this->wArgs, $this->oArgs);
    }
    public function bulidSelect()
    {
        return "SELECT {$this->fStr} FROM {$this->table}{$this->jStr} {$this->wStr} {$this->oStr} {$this->lStr}";
    } 

	



}