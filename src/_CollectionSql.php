<?php namespace dbm;

class CollectionSql
{
    public $sql=[
        'jStr'=>'','gStr'=>'', 'wStr'=>'','lStr'=>'','oStr'=>'','fStr'=>'*' ,
        'rArgs'=>[],'wArgs'=>[], 'fArgs'=>[], 'sArgs'=>[],'oArgs'=>[]
    ];//[join,where,group,order,limit]
    public $hash;

 
    public $table;
    public $pks=[];
    public $ref=[];

    public function __toString()
    {
        if(empty($this->hash)){
            $this->hash = Connect::bulidSql($this->bulidSelect()).';'.join($this->bulidArgs(), ',');
        }
        return $this->hash;
    }     
    
    
    public function find(...$pks){
        $this->sql->find(...$pkv);
        return $this;
    }


    /**
     * ... LIMIT {$limit} OFFSET {$offset} ...
     * @param int $limit
     * @param int $offset
     * @return Pql
     */
    public function limit($limit,$offset=0){
        $this->sql->limit($limit, $offset);
        return $this;
    } 
 
    /**
     * ... ORDER {$order} ...
     * @param string $order
     * @param array ...$arr
     * @return Pql
     */
    public function order($order){ 
        $this->sql['oStr']=" ORDER BY ".$order;
        $this->sql['oArgs']=$arr;
        return $this->uncache();
    }

 
    /**
     * SELECT {$fileds} FROM ...
     * @param string|array $fields
     * @return Pql
     */    
    public function field($fields){
		if(is_array($fields)){
			$this->sql['fStr']=join($fields,',');
		}
		else{
			$this->sql['fStr']=$fields;
		} 
        return $this->uncache(); 
    } 
 

    /**
     * ... WHERE {$w} ...
     * @param string|array $w
     * @param array ...$arr
     * @return Pql
     */ 
    public function where($str,$arr){
        if(!empty($str)){
            $this->sql['wArgs']=[];
            $this->sql['wStr']=" WHERE (".$this->kvSQL($this['wArgs'], ' AND ', $str, $arr).")";
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
            $this->sql['wStr'].=empty($this->sql['wStr'])?" WHERE ":" AND ";
            $this->sql['wStr'].="(".$this->kvSQL($this->sql['wArgs'], ' AND ', $w, $arr).")"; 
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
            $this->sql['wStr'].=empty($this->sql['wStr'])?" WHERE ":" OR ";
            $this->sql['wStr'].="(".$this->kvSQL($this->sql['wArgs'], ' OR ', $w, $arr).")";
        }
        return $this->uncache();  
    }  
    /**
     * ... FROM [TABLE] JOIN {$str} ...
     * @param string  $str
     * @return Pql
     */
    public function join($str)
    {
        $this->sql['jStr']=" $str";
        return $this->uncache();
    }
    /**
     * ... GROUP BY {$str} ...
     * @param string  $str
     * @return Pql
     */
    public function group($str)
    {
        $this->sql['gStr']=" GROUP BY $str";
        return $this->uncache();
    }
    
    private function uncache()
    {
        $this->hash=null;
        return $this;
    }
    private function kvSQL(&$param, $jtag = ' AND ', $arr, $attr = null, $sql = '')
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
    ///laji Session 
	public function bulidArgs()
    { 
        $s = $this->sql;
        return array_merge($s['wArgs'],$s['oArgs']);
    }
    ///laji Session 
    public function bulidSelect()
    {
        $s = $this->sql;
        $table = $this->table;
        return "SELECT {$s['fStr']} FROM {$table}{$s['jStr']} {$s['wStr']}{$s['gStr']} {$s['oStr']} {$s['lStr']}";
    } 

    /////////////////  session /////////////////// 

    function select($all=false)
    {
        $hash = (string)$this; 
        if (!isset($this->session->cache[$hash])) { 
            $ssql = $this->bulidSelect();
            $args = $this->bulidArgs();
			$fetch = $this->session->conn->execute($ssql, $args);
			$fetch->setFetchMode(\PDO::FETCH_OBJ);
			$this->session->cache[$hash]=$fetch->fetchAll(); 
        } 
		if($all or empty($this->parent)){
			return $this->session->cache[$hash];
		}else{ 
            $list = $this->parent->select(); 
            $filter = $list[$this->parent->offset];
			foreach ($this->session->cache[$hash] as $index=>$row) {
				foreach ($this->ref as $k => $v) {
					if ($row->$k!=$filter->$v) {
						continue 2;
					}
				}
				$arr[$index]=$row;
			}
			return $arr??[];
		} 
    } 

  





}